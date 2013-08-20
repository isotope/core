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
        $this->arrData['type'] = array_search(get_called_class(), static::$arrModelTypes);

        if ($this->arrData['type'] == '') {
            throw new \RuntimeException(get_called_class() . ' is not a registered model type');
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
        $strClass = static::$arrModelTypes[$objResult->type];

        if ($strClass == '') {
	        return null;
        }

        $objModel = new $strClass($objResult);

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
        if (static::$strTable == '')
        {
            return null;
        }

        // if find() method is called in a specific model type, results must be of that type
        if (($strType = array_search(get_called_class(), static::getModelTypes())) !== false)
        {
            // Convert to array if necessary
            $arrOptions['value'] = (array) $arrOptions['value'];

            if (!is_array($arrOptions['column']))
            {
                $arrOptions['column'] = array($arrOptions['column'].'=?');
            }

            $arrOptions['column'][] = static::$strTable . '.type=?';
            $arrOptions['value'][] = $strType;
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = \Model\QueryBuilder::find($arrOptions);

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

        // Optionally execute (un)cached (see #5102)
        if (isset($arrOptions['cached']) && $arrOptions['cached'])
        {
            $objResult = $objStatement->executeCached($arrOptions['value']);
        }
        elseif (isset($arrOptions['uncached']) && $arrOptions['uncached'])
        {
            $objResult = $objStatement->executeUncached($arrOptions['value']);
        }
        else
        {
            $objResult = $objStatement->execute($arrOptions['value']);
        }

        if ($objResult->numRows < 1)
        {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {

            return static::buildModelType($objResult);
        } else {

            return new \Isotope\Model\Collection\TypeAgent($objResult, get_called_class());
        }
    }
}
