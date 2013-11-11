<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
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


    public function __construct(\Database\Result $objResult=null)
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
     * @param   string
     * @param   string
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
     * @param   string
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
     * @param   string
     * @return  string
     */
    public static function getClassForModelType($strName)
    {
        return static::$arrModelTypes[$strName];
    }

    /**
     * Return options list of model types
     * @return  array
     */
    public static function getModelTypeOptions()
    {
        $arrOptions = array();

        foreach (static::getModelTypes() as $strName => $strClass) {
            $arrOptions[$strName] = $GLOBALS['TL_LANG']['MODEL'][static::$strTable . '.' . $strName][0] ?: $strName;
        }

        return $arrOptions;
    }

    /**
     * Build model based on database result
     * @param   Database_Result
     */
    public static function buildModelType(\Database_Result $objResult=null)
    {
        $strClass = '';

        if (is_numeric($objResult->type)) {
            $objRelations = new \DcaExtractor(static::$strTable);
            $arrRelations = $objRelations->getRelations();

            if (isset($arrRelations['type'])) {
                $strTypeClass = static::getClassFromTable($arrRelations['type']['table']);
                $objType = $strTypeClass::findOneBy($arrRelations['type']['field'], $objResult->type);

                if (null !== $objType) {
                    $strClass = static::$arrModelTypes[$objType->class];
                }
            }
        } else {
            $strClass = static::$arrModelTypes[$objResult->type];
        }

        // Try to use the current class as fallback
        if ($strClass == '') {
            $strClass = get_called_class();
        }

        $strPk = static::$strPk;
        $intPk = $objResult->$strPk;

        // Try to load from the registry
        $objModel = \Model\Registry::getInstance()->fetch(static::$strTable, $intPk);

        if ($objModel !== null) {
            $objModel->mergeRow($objResult->row());
        } else {
            $objModel = new $strClass($objResult);
        }

        if (null !== static::$strInterface && !is_a($objModel, static::$strInterface)) {
            throw new \RuntimeException(get_class($objModel) . ' must implement interface ' . static::$strInterface);
        }

        return $objModel;
    }

    /**
     * Return a model or collection based on the database result type
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

            if (!is_array($arrOptions['column']))
            {
                $arrOptions['column'] = array(static::$strTable . '.' . $arrOptions['column'].'=?');
            }

            $objRelations = new \DcaExtractor(static::$strTable);
            $arrRelations = $objRelations->getRelations();

            if (isset($arrRelations['type'])) {
                $arrOptions['column'][] = static::$strTable . '.type IN (SELECT ' . $arrRelations['type']['field'] . ' FROM ' . $arrRelations['type']['table'] . ' WHERE class=?)';
                $arrOptions['value'][] = $strType;
            } elseif ($GLOBALS['TL_DCA'][static::$strTable]['fields']['type']) {
                $arrOptions['column'][] = static::$strTable . '.type=?';
                $arrOptions['value'][] = $strType;
            }
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = static::buildQueryString($arrOptions);

        $objStatement = \Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit']))
        {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset']))
        {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
        {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1)
        {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {

			return static::buildModelType($objResult);
		} else {

			return static::createCollectionFromDbResult($objResult);
		}
    }

    /**
     * Allow to override the query builder
     * @param   array
     * @return  string
     */
    protected static function buildQueryString($arrOptions)
    {
        return \Model\QueryBuilder::find($arrOptions);
    }

    /**
     * Create array of models and return a collection of them
     * @param   Database\Result
     * @return  Model\Collection
     */
    protected static function createCollectionFromDbResult($objResult)
    {
        $arrModels = array();

		while ($objResult->next())
		{
		    $objModel = static::buildModelType($objResult);

		    if (null !== $objModel) {
    		    $arrModels[] = $objModel;
		    }
		}

		return new \Model\Collection($arrModels, static::$strTable);
    }
}
