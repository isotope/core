<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Database;
use Contao\Database\Result;
use Contao\DcaExtractor;
use Contao\Model;
use Contao\Model\Collection;
use Contao\Model\Registry;

/**
 * Class TypeAgent
 * Parent class for Isotope Type Agent models.
 */
abstract class TypeAgent extends Model
{

    /**
     * List of types (classes) for this model
     * Must be added to each child class!!
     * @var array
     */
    protected static $arrModelTypes;

    /**
     * Interface to validate the type against
     * @var string
     */
    protected static $strInterface;

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException if model does not have a valid type
     */
    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        // Register model type
        if (!isset($this->arrRelations['type'])) {
            $strType = array_search(static::class, static::$arrModelTypes);

            if ($strType != '') {
                $this->arrData['type'] = $strType;
            }
        }

        if ($this->arrData['type'] == '') {
            throw new \RuntimeException(sprintf(
                '%s (%s.%s) has no model type',
                static::class, static::$strTable, $this->arrData['id']
            ));
        }
    }

    /**
     * Register a model type
     *
     * @param string $strName
     * @param string $strClass
     *
     * @throws \LogicException when called on the TypeAgent class
     */
    public static function registerModelType($strName, $strClass)
    {
        if (null === static::$arrModelTypes) {
            throw new \LogicException('static::$arrModelTypes must be defined in a child class');
        }

        static::$arrModelTypes[$strName] = $strClass;
    }

    /**
     * Unregister a model type
     *
     * @param string $strName
     *
     * @throws \LogicException when called on the TypeAgent class
     */
    public static function unregisterModelType($strName)
    {
        if (null === static::$arrModelTypes) {
            throw new \LogicException('static::$arrModelTypes must be defined in child class');
        }

        unset(static::$arrModelTypes[$strName]);
    }

    /**
     * Get list of model types
     *
     * @return array
     */
    public static function getModelTypes()
    {
        return static::$arrModelTypes;
    }

    /**
     * Get class name for given model type
     *
     * @param string $strName
     *
     * @return string|null
     */
    public static function getClassForModelType($strName)
    {
        return static::$arrModelTypes[$strName] ?? null;
    }

    /**
     * Return options list of model types
     *
     * @return array
     */
    public static function getModelTypeOptions()
    {
        $arrOptions = array();

        foreach (static::getModelTypes() as $strName => $strClass) {
            $arrOptions[$strName] = $GLOBALS['TL_LANG']['MODEL'][static::$strTable][$strName][0] ?? $strName;
        }

        return $arrOptions;
    }

    public static function findByPk($varValue, array $arrOptions = [])
    {
        $result = parent::findByPk($varValue, $arrOptions);

        if (null !== $result && !\is_a($result, static::class, true)) {
            return null;
        }

        return $result;
    }

    public static function findByIdOrAlias($varId, array $arrOptions = [])
    {
        $result = parent::findByIdOrAlias($varId, $arrOptions);

        if (null !== $result && !\is_a($result, static::class, true)) {
            return null;
        }

        return $result;
    }

    /**
     * Find sibling records by a column value
     *
     * @param string $strColumn
     *
     * @return Model|Collection|null
     */
    public static function findSiblingsBy($strColumn, Model $objModel, array $arrOptions=array())
    {
        $t = static::getTable();

        $arrOptions = array_merge(
            array(
                'column' => array(
                    "$t.type=?",
                    "$t.$strColumn=?",
                    "$t.id!=?"
                ),
                'value' => array(
                    $objModel->type,
                    $objModel->{$strColumn},
                    $objModel->id
                ),
                'return'    => 'Collection'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Return a model or collection based on the database result type
     *
     *
     * @return Model|Collection|array|null
     */
    protected static function find(array $arrOptions)
    {
        if (empty(static::$strTable)) {
            throw new \RuntimeException('Empty $strTable property on '.self::class);
        }

        // if the find() method is called in a specific model type, results must be of that type
        if (($strType = array_search(static::class, static::getModelTypes())) !== false) {

            // Convert to array if necessary
            $arrOptions['value'] = (array) $arrOptions['value'];

            if (!\is_array($arrOptions['column'])) {
                $arrOptions['column'] = array(static::$strTable . '.' . $arrOptions['column'] . '=?');
            }

            $objRelations = DcaExtractor::getInstance(static::$strTable);
            $arrRelations = $objRelations->getRelations();
            $arrFields = $objRelations->getFields();

            // @deprecated use string instead of array for HAVING (introduced in Contao 3.3)
            if (!empty($arrOptions['having']) && \is_array($arrOptions['having'])) {
                $arrOptions['having'] = implode(' AND ', $arrOptions['having']);
            }

            if (isset($arrRelations['type'])) {
                $arrOptions['having'] = (empty($arrOptions['having']) ? '' : ' AND ') . 'type IN (SELECT ' . $arrRelations['type']['field'] . ' FROM ' . $arrRelations['type']['table'] . ' WHERE class=?)';
                $arrOptions['value'][]  = $strType;
            } elseif (isset($arrFields['type'])) {
                $arrOptions['having'] = (empty($arrOptions['having']) ? '' : ' AND ') . 'type=?';
                $arrOptions['value'][]  = $strType;
            }
        }

        $arrOptions['table'] = static::$strTable;
        // @deprecated use static::buildFindQuery once we drop BC support for buildQueryString
        $strQuery = static::buildQueryString($arrOptions);

        $objStatement = Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit'])) {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset'])) {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0) {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult    = $objStatement->execute($arrOptions['value'] ?? null);

        if ($objResult->numRows < 1) {
            return 'Array' === $arrOptions['return'] ? array() : null;
        }

        $objResult = static::postFind($objResult);

        if ('Model' === $arrOptions['return']) {
            // @deprecated use static::createModelFromDbResult once we drop BC support for buildModelType
            return static::buildModelType($objResult);
        }

        if ('Array' === $arrOptions['return']) {
            return static::createCollectionFromDbResult($objResult, static::$strTable)->getModels();
        }

        return static::createCollectionFromDbResult($objResult, static::$strTable);
    }

    protected static function createCollection(array $arrModels, $strTable)
    {
        $arrModels = array_filter($arrModels, static function ($model) {
            return \is_a($model, static::class, true);
        });

        return parent::createCollection($arrModels, $strTable);
    }

    /**
     * Build model based on database result
     *
     * @return Model
     */
    public static function createModelFromDbResult(Result $objResult)
    {
        $strClass = '';

        if (is_numeric($objResult->type)) {
            $objRelations = DcaExtractor::getInstance(static::$strTable);
            $arrRelations = $objRelations->getRelations();

            if (isset($arrRelations['type'])) {
                /** @var static $strTypeClass */
                $strTypeClass = static::getClassFromTable($arrRelations['type']['table']);
                $objType      = $strTypeClass::findOneBy($arrRelations['type']['field'], $objResult->type);

                if (null !== $objType) {
                    $strClass = static::getClassForModelType($objType->class);
                }
            }
        } else {
            $strClass = static::getClassForModelType($objResult->type);
        }

        // Try to use the current class as fallback
        if ($strClass == '') {
            $strClass = static::class;

            $objReflection = new \ReflectionClass($strClass);
            if ($objReflection->isAbstract()) {
                return null;
            }
        }

        $objModel = new $strClass($objResult);

        if (null !== static::$strInterface && !is_a($objModel, static::$strInterface)) {
            throw new \RuntimeException(\get_class($objModel) . ' must implement interface ' . static::$strInterface);
        }

        return $objModel;
    }

    /**
     * Create array of models and return a collection of them
     *
     * @param string $strTable
     *
     * @return Collection
     */
    protected static function createCollectionFromDbResult(Result $objResult, $strTable = null)
    {
        // @deprecated Remove in Isotope 3.0 (only for backward compatibility Isotope < 2.1.2)
        if (null === $strTable) {
            $strTable = static::$strTable;
        }

        $arrModels = array();

        while ($objResult->next()) {

            // @deprecated use static::createModelFromDbResult once we drop BC support for buildModelType
            $objModel = static::buildModelType($objResult);

            if (null !== $objModel) {
                $arrModels[] = $objModel;
            }
        }

        return new Collection($arrModels, $strTable);
    }

    /**
     * Build model based on database result
     *
     * @return Model
     *
     * @deprecated use createModelFromDbResult in Contao 3.3
     */
    public static function buildModelType(Result $objResult = null)
    {
        if (null === $objResult) {
            return null;
        }

        $strPk = static::$strPk;
        $intPk = $objResult->$strPk;

        // Try to load from the registry
        /** @var Model $objModel */
        $objModel = Registry::getInstance()->fetch(static::$strTable, $intPk);

        if ($objModel !== null) {
            $objModel->mergeRow($objResult->row());
            return $objModel;
        }

        return static::createModelFromDbResult($objResult);
    }

    /**
     * Allow to override the query builder
     *
     * @param array $arrOptions
     *
     * @return string
     *
     * @deprecated use buildFindQuery introduced in Contao 3.3
     */
    protected static function buildQueryString($arrOptions)
    {
        return static::buildFindQuery($arrOptions);
    }
}
