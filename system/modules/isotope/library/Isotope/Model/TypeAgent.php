<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;


/**
 * Class TypeAgent
 *
 * Parent class for Isotope Type Agent models.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class TypeAgent extends \Model
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


    public function __construct(\Database\Result $objResult = null)
    {
        parent::__construct($objResult);

        // Register model type
        if (!isset($this->arrRelations['type'])) {
            $strType = array_search(get_called_class(), static::$arrModelTypes);

            if ($strType != '') {
                $this->arrData['type'] = $strType;
            }
        }

        if ($this->arrData['type'] == '') {
            throw new \RuntimeException(get_called_class() . ' has no model type');
        }
    }

    /**
     * Register a model type
     *
     * @param   string $strName
     * @param   string $strClass
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
     * @param   string $strName
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
     * @return  array
     */
    public static function getModelTypes()
    {
        return static::$arrModelTypes;
    }

    /**
     * Get class name for given model type
     *
     * @param   string $strName
     *
     * @return  string
     */
    public static function getClassForModelType($strName)
    {
        return static::$arrModelTypes[$strName];
    }

    /**
     * Return options list of model types
     *
     * @return  array
     */
    public static function getModelTypeOptions()
    {
        $arrOptions = array();

        foreach (static::getModelTypes() as $strName => $strClass) {
            $arrOptions[$strName] = $GLOBALS['TL_LANG']['MODEL'][static::$strTable . '.' . $strName][0] ? : $strName;
        }

        return $arrOptions;
    }

    /**
     * Find sibling records by a column value
     *
     * @param   string
     * @param   \Model
     * @param   array
     *
     * @return \Model|\Model\Collection|null
     */
    public static function findSiblingsBy($strColumn, \Model $objModel, array $arrOptions=array())
    {
        $t = static::getTable();

        $arrOptions = array_merge(
            array(
                'column'    => array(
                    "$t.type=?",
                    "$t.$strColumn=?",
                    "$t.id!=?"
                ),
                'value'     => array(
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
     * @param array $arrOptions
     *
     * @return \Model|\Model\Collection|null
     */
    protected static function find(array $arrOptions)
    {
        if (static::$strTable == '') {
            return null;
        }

        // if find() method is called in a specific model type, results must be of that type
        if (($strType = array_search(get_called_class(), static::getModelTypes())) !== false) {

            // Convert to array if necessary
            $arrOptions['value'] = (array) $arrOptions['value'];

            if (!is_array($arrOptions['column'])) {
                $arrOptions['column'] = array(static::$strTable . '.' . $arrOptions['column'] . '=?');
            }

            $objRelations = new \DcaExtractor(static::$strTable);
            $arrRelations = $objRelations->getRelations();
            $arrFields = $objRelations->getFields();

            // @deprecated use string instead of array for HAVING (introduced in Contao 3.3)
            if (!empty($arrOptions['having']) && is_array($arrOptions['having'])) {
                $arrOptions['having'] = implode(' AND ', $arrOptions['having']);
            }

            if (isset($arrRelations['type'])) {
                $arrOptions['having'] = (empty($arrOptions['having']) ? '' : ' AND ') . 'type IN (SELECT ' . $arrRelations['type']['field'] . ' FROM ' . $arrRelations['type']['table'] . ' WHERE class=?)';
                $arrOptions['value'][]  = $strType;
            } elseif (isset($arrFields['type'])) {
                $arrOptions['having'] = (empty($arrOptions['having']) ? '' : ' AND ') . 'type=?';
                $arrOptions['value'][]  = $strType;
            }

            // @deprecated remove when we drop support for Contao 3.2
            if (version_compare(VERSION, '3.3', '<')) {
                if ($arrOptions['group'] !== null) {
                    $arrOptions['group'] .= ' HAVING ' . $arrOptions['having'];
                } else {
                    $arrOptions['column'][] = '1=1 HAVING ' . $arrOptions['having'];
                }
            }
        }

        $arrOptions['table'] = static::$strTable;
        // @deprecated use static::buildFindQuery once we drop BC support for buildQueryString
        $strQuery            = static::buildQueryString($arrOptions);

        $objStatement = \Database::getInstance()->prepare($strQuery);

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
        $objResult    = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1) {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {

            // @deprecated use static::createModelFromDbResult once we drop BC support for buildModelType
            return static::buildModelType($objResult);
        } else {

            return static::createCollectionFromDbResult($objResult, static::$strTable);
        }
    }

    /**
     * Build model based on database result
     *
     * @param \Database\Result $objResult
     *
     * @return \Model
     */
    public static function createModelFromDbResult(\Database\Result $objResult)
    {
        $strClass = '';

        if (is_numeric($objResult->type)) {
            $objRelations = new \DcaExtractor(static::$strTable);
            $arrRelations = $objRelations->getRelations();

            if (isset($arrRelations['type'])) {
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
            $strClass = get_called_class();

            $objReflection = new \ReflectionClass($strClass);
            if ($objReflection ->isAbstract()) {
                return null;
            }
        }

        $objModel = new $strClass($objResult);

        if (null !== static::$strInterface && !is_a($objModel, static::$strInterface)) {
            throw new \RuntimeException(get_class($objModel) . ' must implement interface ' . static::$strInterface);
        }

        return $objModel;
    }

    /**
     * Create array of models and return a collection of them
     *
     * @param   \Database\Result $objResult
     * @param   string           $strTable
     *
     * @return  \Model\Collection
     */
    protected static function createCollectionFromDbResult(\Database\Result $objResult, $strTable = null)
    {
        // @deprecated only for backward compatibility with Contao 3.2/Isotope < 2.1.2
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

        return new \Model\Collection($arrModels, $strTable);
    }

    /**
     * Build model based on database result
     *
     * @param \Database\Result $objResult
     *
     * @return \Model
     * @deprecated  use createModelFromDbResult in Contao 3.3
     */
    public static function buildModelType(\Database\Result $objResult = null)
    {
        if (null === $objResult) {
            return null;
        }

        $strPk = static::$strPk;
        $intPk = $objResult->$strPk;

        // Try to load from the registry
        /** @var \Model $objModel */
        $objModel = \Model\Registry::getInstance()->fetch(static::$strTable, $intPk);

        if ($objModel !== null) {
            $objModel->mergeRow($objResult->row());
            return $objModel;
        }

        return static::createModelFromDbResult($objResult);
    }

    /**
     * Build a query based on the given options
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     * @deprecated this is only for BC with Contao 3.2
     */
    protected static function buildFindQuery(array $arrOptions)
    {
        if (version_compare(VERSION, '3.3', '<')) {
            return \Model\QueryBuilder::find($arrOptions);
        }

        return parent::buildFindQuery($arrOptions);
    }

    /**
     * Allow to override the query builder
     *
     * @param       array
     *
     * @return      string
     * @deprecated  use buildFindQuery introduced in Contao 3.3
     */
    protected static function buildQueryString($arrOptions)
    {
        return static::buildFindQuery($arrOptions);
    }
}
