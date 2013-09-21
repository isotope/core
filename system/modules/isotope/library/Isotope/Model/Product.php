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

use Isotope\Isotope;
use Isotope\Model\Attribute;


/**
 * The basic Isotope product model
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Product extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_products';

    /**
     * Interface to validate attribute
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeProduct';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Find all published products
     * @param   array
     * @return  \Collection
     */
    public static function findPublished(array $arrOptions=array())
    {
        $t = static::$strTable;

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

        return static::findBy($arrColumns, array(), $arrOptions);
    }

    /**
     * Find published products by condition
     * @param   mixed
     * @param   mixed
     * @param   array
     * @return  \Collection
     */
    public static function findPublishedBy($arrColumns, $arrValues, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrValues = (array) $arrValues;

        if (!is_array($arrColumns)) {
            $arrColumns = array(static::$strTable . '.' . $arrColumns . '=?');
        }

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }

    /**
     * Find a single product by primary key
     * @param   int
     * @param   array
     * @return  \Collection
     */
    public static function findPublishedByPk($intId, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrColumns = array("$t." . static::$strPk . "=?");
        $arrValues = array((int) $intId);

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

		$arrOptions = array_merge(
			array(
				'limit'  => 1,
				'column' => $arrColumns,
				'value'  => $arrValues,
				'return' => 'Model'
			),
			$arrOptions
		);

		return static::find($arrOptions);
    }

    /**
	 * Find a single product by its ID or alias
	 * @param mixed $varId      The ID or alias
	 * @param array $arrOptions An optional options array
	 * @return \Model|null The model or null if the result is empty
	 */
	public static function findPublishedByIdOrAlias($varId, array $arrOptions=array())
	{
		$t = static::$strTable;

		$arrColumns = array("($t.id=? OR $t.alias=?)");
        $arrValues = array((is_numeric($varId) ? $varId : 0), $varId);

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

		$arrOptions = array_merge(
			array(
				'limit'  => 1,
				'column' => $arrColumns,
				'value'  => $arrValues,
				'return' => 'Model'
			),
			$arrOptions
		);

		return static::find($arrOptions);
	}

	/**
     * Find products by IDs
     * @param   array
     * @param   array
     * @return  \Collection
     */
    public static function findPublishedById(array $arrIds, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrColumns = array("$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")");

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

		$arrOptions = array_merge(
			array(
				'column' => $arrColumns,
				'return' => 'Collection'
			),
			$arrOptions
		);

		return static::find($arrOptions);
    }

    /**
     * Return collection of published product variants by product PID
     * @param   int
     * @param   array
     * @return  \Collection
     */
    public static function findPublishedByPid($intPid, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrColumns = array("$t.pid=?");

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

		$arrOptions = array_merge(
			array(
				'column' => $arrColumns,
				'value'  => array((int) $intPid),
				'return' => 'Collection'
			),
			$arrOptions
		);

		return static::find($arrOptions);
    }

    /**
     * Return collection of published products by categories
     * @param   array
     * @param   array
     * @return  \Collection
     */
    public static function findPublishedByCategories(array $arrCategories, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrColumns = array("c.page_id IN (" . implode(',', array_map('intval', $arrCategories)) . ")");

        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)";
        }

		$arrOptions = array_merge(
			array(
				'column' => $arrColumns,
				'return' => 'Collection'
			),
			$arrOptions
		);

		return static::find($arrOptions);
    }

    /**
     * Return select statement to load product data including multilingual fields
     * @param array an array of columns
     * @return string
     */
    protected static function buildQueryString($arrOptions, $arrJoinAliases=array('t'=>'tl_iso_producttypes'))
    {
        $objBase = new \DcaExtractor($arrOptions['table']);

        $arrJoins = array();
        $arrFields = array($arrOptions['table'] . ".*", "'". str_replace('-', '_', $GLOBALS['TL_LANGUAGE']) . "' AS language");

        foreach (Attribute::getMultilingualFields() as $attribute)
        {
            $arrFields[] = "IFNULL(translation.$attribute, " . $arrOptions['table'] . ".$attribute) AS $attribute";
        }

        foreach (Attribute::getFetchFallbackFields() as $attribute)
        {
            $arrFields[] = "{$arrOptions['table']}.$attribute AS {$attribute}_fallback";
        }

        $arrFields[] = "c.sorting";

        $arrJoins[] = " LEFT OUTER JOIN tl_iso_product_categories c ON {$arrOptions['table']}.id=c.pid";
        $arrJoins[] = " LEFT OUTER JOIN " . $arrOptions['table'] . " translation ON " . $arrOptions['table'] . ".id=translation.pid AND translation.language='" . str_replace('-', '_', $GLOBALS['TL_LANGUAGE']) . "'";


        if ($objBase->hasRelations()) {

            $intCount = 0;

            foreach ($objBase->getRelations() as $strKey=>$arrConfig)
            {
                // Automatically join the single-relation records
                if ($arrConfig['load'] == 'eager' || $arrOptions['eager'])
                {
                    if ($arrConfig['type'] == 'hasOne' || $arrConfig['type'] == 'belongsTo')
                    {
                        $key = array_search($arrConfig['table'], $arrJoinAliases);
                        if (false !== $key) {
                            $strJoinAlias = $key;
                            unset($arrJoinAliases[$key]);
                        } else {
                            ++$intCount;
                            $strJoinAlias = 'j' . $intCount;
                        }

                        $objRelated = new \DcaExtractor($arrConfig['table']);

                        foreach (array_keys($objRelated->getFields()) as $strField)
                        {
                            $arrFields[] = $strJoinAlias . '.' . $strField . ' AS ' . $strKey . '__' . $strField;
                        }

                        $arrJoins[] = " LEFT JOIN " . $arrConfig['table'] . " $strJoinAlias ON " . $arrOptions['table'] . "." . $strKey . "=$strJoinAlias.id";
                    }
                }
            }
        }

        // Generate the query
        $strQuery = "SELECT " . implode(', ', $arrFields) . " FROM " . $arrOptions['table'] . implode("", $arrJoins);

        // Where condition
        if (!is_array($arrOptions['column']))
        {
            $arrOptions['column'] = array($arrOptions['table'] . '.' . $arrOptions['column'].'=?');
        }

        // The model must never find a language record
        $arrOptions['column'][] = "{$arrOptions['table']}.language=''";

        $strQuery .= " WHERE " . implode(" AND ", $arrOptions['column']);

        // Group by
        if ($arrOptions['group'] !== null)
        {
            $strQuery .= " GROUP BY " . $arrOptions['group'];
        }

        // Order by
        if ($arrOptions['order'] !== null)
        {
            $strQuery .= " ORDER BY " . $arrOptions['order'];
        }

        return $strQuery;
    }
}
