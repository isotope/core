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

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;


/**
 * The basic Isotope product model
 *
 * @property int    id
 * @property int    pid
 * @property int    gid
 * @property int    tstamp
 * @property string language
 * @proeprty int    dateAdded
 * @property int    type
 */
abstract class Product extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_product';

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
     * Currently active product (LIFO queue)
     * @var array
     */
    protected static $arrActive = array();

    /**
     * Get product that is currently active (needed e.g. for insert tag replacement)
     * @return   IsotopeProduct|null
     */
    public static function getActive()
    {
        return empty(static::$arrActive) ? null : end(static::$arrActive);
    }

    /**
     * Set product that is currently active (needed e.g. for insert tag replacement)
     * @param   IsotopeProduct|null
     */
    public static function setActive(IsotopeProduct $objProduct)
    {
        array_push(static::$arrActive, $objProduct);
    }

    /**
     * Unset product that is currently active (prevent later use of it)
     * @param   IsotopeProduct
     */
    public static function unsetActive()
    {
        array_pop(static::$arrActive);
    }

    /**
     * Find all published products
     * @param   array
     * @return  \Model\Collection
     */
    public static function findPublished(array $arrOptions = array())
    {
        return static::findPublishedBy(array(), array(), $arrOptions);
    }

    /**
     * Find published products by condition
     * @param   mixed
     * @param   mixed
     * @param   array
     * @return  \Model\Collection
     */
    public static function findPublishedBy($arrColumns, $arrValues, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrValues = (array) $arrValues;

        if (!is_array($arrColumns)) {
            $arrColumns = array(static::$strTable . '.' . $arrColumns . '=?');
        }

        // Add publish check to $arrColumns as the first item to enable SQL keys
        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            array_unshift($arrColumns, "$t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)");
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }

    /**
     * Find a single product by primary key
     * @param   int
     * @param   array
     * @return  \Model\Collection
     */
    public static function findPublishedByPk($intId, array $arrOptions = array())
    {
        $arrOptions = array_merge(
            array(
                'return'    => 'Model'
            ),
            $arrOptions
        );

        return static::findPublishedBy(static::$strPk, (int) $intId, $arrOptions);
    }

    /**
     * Find a single product by its ID or alias
     * @param mixed $varId The ID or alias
     * @param array $arrOptions An optional options array
     * @return \Model|null The model or null if the result is empty
     */
    public static function findPublishedByIdOrAlias($varId, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrColumns = array("($t.id=? OR $t.alias=?)");
        $arrValues  = array((is_numeric($varId) ? $varId : 0), $varId);

        $arrOptions = array_merge(
            array(
                'limit'     => 1,
                'return'    => 'Model'
            ),
            $arrOptions
        );

        return static::findPublishedBy($arrColumns, $arrValues, $arrOptions);
    }

    /**
     * Find products by IDs
     * @param   array
     * @param   array
     * @return  \Model\Collection
     */
    public static function findPublishedByIds(array $arrIds, array $arrOptions = array())
    {
        if (empty($arrIds) || !is_array($arrIds)) {
            return null;
        }

        return static::findPublishedBy(array(static::$strTable . '.id IN (' . implode(',', array_map('intval', $arrIds)) . ')'), null, $arrOptions);
    }

    /**
     * Return collection of published product variants by product PID
     * @param   int
     * @param   array
     * @return  \Model\Collection
     */
    public static function findPublishedByPid($intPid, array $arrOptions = array())
    {
        return static::findPublishedBy('pid', (int) $intPid, $arrOptions);
    }

    /**
     * Return collection of published products by categories
     * @param   array
     * @param   array
     * @return  \Model\Collection
     */
    public static function findPublishedByCategories(array $arrCategories, array $arrOptions = array())
    {
        return static::findPublishedBy(array("c.page_id IN (" . implode(',', array_map('intval', $arrCategories)) . ")"), null, $arrOptions);
    }

    /**
     * Find a single frontend-available product by primary key
     * @param   int
     * @param   array
     * @return  \Model\Collection
     */
    public static function findAvailableByPk($intId, array $arrOptions = array())
    {
        $objProduct = static::findPublishedByPk($intId, $arrOptions);

        if (null === $objProduct || !$objProduct->isAvailableInFrontend()) {
            return null;
        }

        return $objProduct;
    }

    /**
     * Find a single frontend-available product by its ID or alias
     * @param   mixed       The ID or alias
     * @param   array       An optional options array
     * @return Product|null  The model or null if the result is empty
     */
    public static function findAvailableByIdOrAlias($varId, array $arrOptions = array())
    {
        $objProduct = static::findPublishedByIdOrAlias($varId, $arrOptions);

        if (null === $objProduct || !$objProduct->isAvailableInFrontend()) {
            return null;
        }

        return $objProduct;
    }

    /**
     * Find frontend-available products by IDs
     * @param   array
     * @param   array
     * @return  \Model\Collection
     */
    public static function findAvailableByIds(array $arrIds, array $arrOptions = array())
    {
        $objProducts = static::findPublishedByIds($arrIds, $arrOptions);

        if (null === $objProducts) {
            return null;
        }

        $arrProducts = array();
        foreach ($objProducts as $objProduct) {
            if ($objProduct->isAvailableInFrontend()) {
                $arrProducts[] = $objProduct;
            }
        }

        if (empty($arrProducts)) {
            return null;
        }

        return new \Model\Collection($arrProducts, static::$strTable);
    }

    /**
     * Find frontend-available products by condition
     * @param   mixed
     * @param   mixed
     * @param   array
     * @return  \Model\Collection
     */
    public static function findAvailableBy($arrColumns, $arrValues, array $arrOptions = array())
    {
        $objProducts = static::findPublishedBy($arrColumns, $arrValues, $arrOptions);

        if (null === $objProducts) {
            return null;
        }

        $arrProducts = array();
        foreach ($objProducts as $objProduct) {
            if ($objProduct->isAvailableInFrontend()) {
                $arrProducts[] = $objProduct;
            }
        }

        if (empty($arrProducts)) {
            return null;
        }

        return new \Model\Collection($arrProducts, static::$strTable);
    }

    /**
     * Find variant of a product
     * @param   IsotopeProduct
     * @param   array
     * @param   array
     */
    public static function findVariantOfProduct(IsotopeProduct $objProduct, array $arrVariant, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrColumns = array(
            "$t.id IN (" . implode(',', $objProduct->getVariantIds()) . ")",
            "$t." . implode("=? AND $t.", array_keys($arrVariant)) . "=?"
        );

        $arrOptions = array_merge(
            array(
                 'limit'  => 1,
                 'column' => $arrColumns,
                 'value'  => $arrVariant,
                 'return' => 'Model'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Return a model or collection based on the database result type
     */
    protected static function find(array $arrOptions)
    {
        $arrOptions['group'] = static::getTable() . '.id' . (null === $arrOptions['group'] ? '' : ', '.$arrOptions['group']);

        $objProducts = parent::find($arrOptions);

        if (null === $objProducts) {
            return null;
        }

        $arrFilters = $arrOptions['filters'];
        $arrSorting = $arrOptions['sorting'];

        if (!empty($arrFilters) || !empty($arrSorting)) {

            $arrProducts = $objProducts->getModels();

            if (!empty($arrFilters)) {
                $arrProducts = array_filter($arrProducts, function ($objProduct) use ($arrFilters) {
                    $arrGroups = array();

                    foreach ($arrFilters as $objFilter) {
                        $blnMatch = $objFilter->matches($objProduct);

                        if ($objFilter->hasGroup()) {
                            $arrGroups[$objFilter->getGroup()] = $arrGroups[$objFilter->getGroup()] ? : $blnMatch;
                        } elseif (!$blnMatch) {
                            return false;
                        }
                    }

                    if (!empty($arrGroups) && in_array(false, $arrGroups)) {
                        return false;
                    }

                    return true;
                });
            }

            // $arrProducts can be empty if the filter removed all records
            if (!empty($arrSorting) && !empty($arrProducts)) {
                $arrParam = array();
                $arrData  = array();

                foreach ($arrSorting as $strField => $arrConfig) {
                    foreach ($arrProducts as $objProduct) {

                        // Both SORT_STRING and SORT_REGULAR are case sensitive, strings starting with a capital letter will come before strings starting with a lowercase letter.
                        // To perform a case insensitive search, force the sorting order to be determined by a lowercase copy of the original value.

                        // Temporary fix for price attribute (see #945)
                        if ($strField == 'price') {
                            $arrData[$strField][$objProduct->id] = ($objProduct->getPrice() !==  null) ? $objProduct->getPrice()->getAmount() : 0;
                        } else {
                            $arrData[$strField][$objProduct->id] = strtolower(str_replace('"', '', $objProduct->$strField));
                        }
                    }

                    $arrParam[] = &$arrData[$strField];
                    $arrParam[] = $arrConfig[0];
                    $arrParam[] = $arrConfig[1];
                }

                // Add product array as the last item. This will sort the products array based on the sorting of the passed in arguments.
                $arrParam[] = &$arrProducts;
                call_user_func_array('array_multisort', $arrParam);
            }

            $objProducts = new \Model\Collection($arrProducts, static::$strTable);
        }

        return $objProducts;
    }

    /**
     * Return select statement to load product data including multilingual fields
     * @param array an array of columns
     * @return string
     */
    protected static function buildFindQuery(array $arrOptions)
    {
        $objBase = new \DcaExtractor($arrOptions['table']);

        $arrJoins  = array();
        $arrFields = array(
            $arrOptions['table'] . ".*",
            "IF(" . $arrOptions['table'] . ".pid>0, parent.type, " . $arrOptions['table'] . ".type) AS type",
            "'" . str_replace('-', '_', $GLOBALS['TL_LANGUAGE']) . "' AS language",
        );

        foreach (Attribute::getMultilingualFields() as $attribute) {
            $arrFields[] = "IFNULL(translation.$attribute, " . $arrOptions['table'] . ".$attribute) AS $attribute";
        }

        foreach (Attribute::getFetchFallbackFields() as $attribute) {
            $arrFields[] = "{$arrOptions['table']}.$attribute AS {$attribute}_fallback";
        }

        $arrFields[] = "c.sorting";

        $arrJoins[] = " LEFT OUTER JOIN " . \Isotope\Model\ProductCategory::getTable() . " c ON {$arrOptions['table']}.id=c.pid";
        $arrJoins[] = " LEFT OUTER JOIN " . $arrOptions['table'] . " translation ON " . $arrOptions['table'] . ".id=translation.pid AND translation.language='" . str_replace('-', '_', $GLOBALS['TL_LANGUAGE']) . "'";
        $arrJoins[] = " LEFT OUTER JOIN " . $arrOptions['table'] . " parent ON " . $arrOptions['table'] . ".pid=parent.id";


        if ($objBase->hasRelations()) {

            $intCount = 0;

            foreach ($objBase->getRelations() as $strKey => $arrConfig) {
                // Automatically join the single-relation records
                if ($arrConfig['load'] == 'eager' || $arrOptions['eager']) {
                    if ($arrConfig['type'] == 'hasOne' || $arrConfig['type'] == 'belongsTo') {

                        if (is_array($arrOptions['joinAliases']) && ($key = array_search($arrConfig['table'], $arrOptions['joinAliases'])) !== false) {
                            $strJoinAlias = $key;
                            unset($arrOptions['joinAliases'][$key]);
                        } else {
                            ++$intCount;
                            $strJoinAlias = 'j' . $intCount;
                        }

                        $objRelated = new \DcaExtractor($arrConfig['table']);

                        foreach (array_keys($objRelated->getFields()) as $strField) {
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
        if (!is_array($arrOptions['column'])) {
            $arrOptions['column'] = array($arrOptions['table'] . '.' . $arrOptions['column'] . '=?');
        }

        // The model must never find a language record
        $strQuery .= " WHERE {$arrOptions['table']}.language='' AND " . implode(" AND ", $arrOptions['column']);

        // Group by
        if ($arrOptions['group'] !== null) {
            $strQuery .= " GROUP BY " . $arrOptions['group'];
        }

        // Order by
        if ($arrOptions['order'] !== null) {
            $strQuery .= " ORDER BY " . $arrOptions['order'];
        }

        return $strQuery;
    }

    /**
     * Return select statement to load product data including multilingual fields
     * @param   array   an array of columns
     * @param   array   an array of table join aliases
     * @return  string
     * @deprecated  use buildFindQuery introduced in Contao 3.3
     */
    protected static function buildQueryString($arrOptions, $arrJoinAliases = array('t' => 'tl_iso_producttype'))
    {
        $arrOptions['joinAliases'] = $arrJoinAliases;

        return static::buildFindQuery((array) $arrOptions);
    }
}
