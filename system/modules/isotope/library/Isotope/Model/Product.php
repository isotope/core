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
use Contao\Date;
use Contao\DcaExtractor;
use Contao\Model;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\RequestCache\Filter;
use Model\Collection;

/**
 * The basic Isotope product model
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $gid
 * @property int    $tstamp
 * @property string $language
 * @property int    $dateAdded
 * @property int    $type
 * @property array  $pages
 * @property array  $orderPages
 * @property array  $inherit
 * @property bool   $fallback
 * @property string $alias
 * @property string $sku
 * @property string $name
 * @property string $teaser
 * @property string $description
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keywords
 * @property bool   $shipping_exempt
 * @property array  $images
 * @property bool   $protected
 * @property array  $groups
 * @property bool   $guests
 * @property array  $cssID
 * @property bool   $published
 * @property string $start
 * @property string $stop
 */
abstract class Product extends TypeAgent implements IsotopeProduct
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
     * Get categories (pages) assigned to this product
     *
     * @param bool $blnPublished Only return published categories (pages)
     *
     * @return array
     */
    abstract public function getCategories($blnPublished = false);

    /**
     * Get product that is currently active (needed e.g. for insert tag replacement)
     *
     * @return IsotopeProduct|null
     */
    public static function getActive()
    {
        return 0 === \count(static::$arrActive) ? null : end(static::$arrActive);
    }

    /**
     * Set product that is currently active (needed e.g. for insert tag replacement)
     *
     * @param IsotopeProduct $objProduct
     */
    public static function setActive(IsotopeProduct $objProduct)
    {
        static::$arrActive[] = $objProduct;
    }

    /**
     * Unset product that is currently active (prevent later use of it)
     */
    public static function unsetActive()
    {
        array_pop(static::$arrActive);
    }

    /**
     * Find all published products
     *
     * @param array $arrOptions
     *
     * @return Collection|Product[]|null
     */
    public static function findPublished(array $arrOptions = array())
    {
        return static::findPublishedBy(array(), array(), $arrOptions);
    }

    /**
     * Find published products by condition
     *
     * @param mixed $arrColumns
     * @param mixed $arrValues
     * @param array $arrOptions
     *
     * @return Collection|Product[]|null
     */
    public static function findPublishedBy($arrColumns, $arrValues, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrValues = (array) $arrValues;

        if (!\is_array($arrColumns)) {
            $arrColumns = array(static::$strTable . '.' . $arrColumns . '=?');
        }

        // Add publish check to $arrColumns as the first item to enable SQL keys
        if (BE_USER_LOGGED_IN !== true) {
            $time = Date::floorToMinute();
            array_unshift(
                $arrColumns,
                "$t.published='1' AND ($t.start='' OR $t.start<'$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "')"
            );
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }

    /**
     * Find a single product by primary key
     *
     * @param int   $intId
     * @param array $arrOptions
     *
     * @return static|null
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
     *
     * @param mixed $varId      The ID or alias
     * @param array $arrOptions An optional options array
     *
     * @return static|null      The model or null if the result is empty
     */
    public static function findPublishedByIdOrAlias($varId, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrColumns = array("($t.id=? OR $t.alias=?)");
        $arrValues  = array(is_numeric($varId) ? $varId : 0, $varId);

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
     *
     * @param array $arrIds
     * @param array $arrOptions
     *
     * @return Product[]|Collection
     */
    public static function findPublishedByIds(array $arrIds, array $arrOptions = array())
    {
        if (0 === \count($arrIds)) {
            return null;
        }

        return static::findPublishedBy(
            array(static::$strTable . '.id IN (' . implode(',', array_map('intval', $arrIds)) . ')'),
            null,
            $arrOptions
        );
    }

    /**
     * Return collection of published product variants by product PID
     *
     * @param int   $intPid
     * @param array $arrOptions
     *
     * @return Collection|Product[]|null
     */
    public static function findPublishedByPid($intPid, array $arrOptions = array())
    {
        return static::findPublishedBy('pid', (int) $intPid, $arrOptions);
    }

    /**
     * Return collection of published products by categories
     *
     * @param array $arrCategories
     * @param array $arrOptions
     *
     * @return Collection|Product[]|null
     */
    public static function findPublishedByCategories(array $arrCategories, array $arrOptions = array())
    {
        return static::findPublishedBy(
            array('c.page_id IN (' . implode(',', array_map('intval', $arrCategories)) . ')'),
            null,
            $arrOptions
        );
    }

    /**
     * Find a single frontend-available product by primary key
     *
     * @param int   $intId
     * @param array $arrOptions
     *
     * @return static|null
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
     *
     * @param mixed $varId      The ID or alias
     * @param array $arrOptions An optional options array
     *
     * @return Product|null     The model or null if the result is empty
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
     *
     * @param array $arrIds
     * @param array $arrOptions
     *
     * @return Collection|Product[]|null
     */
    public static function findAvailableByIds(array $arrIds, array $arrOptions = array())
    {
        $objProducts = static::findPublishedByIds($arrIds, $arrOptions);

        if (null === $objProducts) {
            return null;
        }

        $arrProducts = [];
        foreach ($objProducts as $objProduct) {
            if ($objProduct->isAvailableInFrontend()) {
                $arrProducts[] = $objProduct;
            }
        }

        if (0 === \count($arrProducts)) {
            return null;
        }

        return new Collection($arrProducts, static::$strTable);
    }

    /**
     * Find frontend-available products by condition
     *
     * @param mixed $arrColumns
     * @param mixed $arrValues
     * @param array $arrOptions
     *
     * @return Collection
     */
    public static function findAvailableBy($arrColumns, $arrValues, array $arrOptions = array())
    {
        $objProducts = static::findPublishedBy($arrColumns, $arrValues, $arrOptions);

        if (null === $objProducts) {
            return null;
        }

        $arrProducts = [];
        foreach ($objProducts as $objProduct) {
            if ($objProduct->isAvailableInFrontend()) {
                $arrProducts[] = $objProduct;
            }
        }

        if (0 === \count($arrProducts)) {
            return null;
        }

        return new Collection($arrProducts, static::$strTable);
    }

    /**
     * Find variant of a product
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrVariant
     * @param array          $arrOptions
     *
     * @return Model|null
     */
    public static function findVariantOfProduct(
        IsotopeProduct $objProduct,
        array $arrVariant,
        array $arrOptions = array()
    ) {
        $t = static::$strTable;

        $arrColumns = array(
            "$t.id IN (" . implode(',', $objProduct->getVariantIds()) . ')',
            "$t." . implode("=? AND $t.", array_keys($arrVariant)) . '=?'
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
     * Finds the default variant of a product.
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrOptions
     *
     * @return static|null
     */
    public static function findDefaultVariantOfProduct(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        static $cache;

        if (null === $cache) {
            $cache = [];
            $data  = Database::getInstance()->execute(
                "SELECT id, pid FROM tl_iso_product WHERE pid>0 AND language='' AND fallback='1'"
            );

            while ($data->next()) {
                $cache[$data->pid] = $data->id;
            }
        }

        $defaultId = $cache[$objProduct->getProductId()];

        if ($defaultId < 1 || !\in_array($defaultId, $objProduct->getVariantIds())) {
            return null;
        }

        return static::findByPk($defaultId, $arrOptions);
    }

    /**
     * Returns the number of published products.
     *
     * @param array $arrOptions
     *
     * @return int
     */
    public static function countPublished(array $arrOptions = array())
    {
        return static::countPublishedBy(array(), array(), $arrOptions);
    }

    /**
     * Return the number of products matching certain criteria
     *
     * @param mixed $arrColumns
     * @param mixed $arrValues
     * @param array $arrOptions
     *
     * @return int
     */
    public static function countPublishedBy($arrColumns, $arrValues, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrValues = (array) $arrValues;

        if (!\is_array($arrColumns)) {
            $arrColumns = array(static::$strTable . '.' . $arrColumns . '=?');
        }

        // Add publish check to $arrColumns as the first item to enable SQL keys
        if (BE_USER_LOGGED_IN !== true) {
            $time = Date::floorToMinute();
            array_unshift(
                $arrColumns,
                "
                    $t.published='1'
                    AND ($t.start='' OR $t.start<'$time')
                    AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "')
                "
            );
        }

        return static::countBy($arrColumns, $arrValues, $arrOptions);
    }

    /**
     * Gets the number of translation records in the product table.
     * Mostly useful to see if there are any translations at all to optimize queries.
     *
     * @return int
     */
    public static function countTranslatedProducts()
    {
        static $result;

        if (null === $result) {
            $result = Database::getInstance()->query(
                "SELECT COUNT(*) AS total FROM tl_iso_product WHERE language!=''"
            )->total;
        }

        return $result;
    }

    /**
     * Return a model or collection based on the database result type
     *
     * @param array $arrOptions
     *
     * @return Product|Product[]|Collection|null
     */
    protected static function find(array $arrOptions)
    {
        $arrOptions['group'] = static::getTable() . '.id' . (null === ($arrOptions['group'] ?? null) ? '' : ', '.$arrOptions['group']);

        $objProducts = parent::find($arrOptions);

        if (null === $objProducts) {
            return null;
        }

        /** @var Filter[] $arrFilters */
        $arrFilters = $arrOptions['filters'] ?? null;
        $arrSorting = $arrOptions['sorting'] ?? null;

        $hasFilters = \is_array($arrFilters) && 0 !== \count($arrFilters);
        $hasSorting = \is_array($arrSorting) && 0 !== \count($arrSorting);

        if ($hasFilters || $hasSorting) {

            /** @var static[] $arrProducts */
            $arrProducts = $objProducts->getModels();

            if ($hasFilters) {
                $arrProducts = array_filter($arrProducts, function ($objProduct) use ($arrFilters) {
                    $arrGroups = [];

                    foreach ($arrFilters as $objFilter) {
                        $blnMatch = $objFilter->matches($objProduct);

                        if ($objFilter->hasGroup()) {
                            $arrGroups[$objFilter->getGroup()] = $arrGroups[$objFilter->getGroup()] ? : $blnMatch;
                        } elseif (!$blnMatch) {
                            return false;
                        }
                    }

                    return !\in_array(false, $arrGroups, true);
                });
            }

            // $arrProducts can be empty if the filter removed all records
            if ($hasSorting && 0 !== \count($arrProducts)) {
                $arrParam = array();
                $arrData  = array();

                foreach ($arrSorting as $strField => $arrConfig) {
                    foreach ($arrProducts as $objProduct) {

                        // Both SORT_STRING and SORT_REGULAR are case sensitive, strings starting with a capital letter
                        // will come before strings starting with a lowercase letter. To perform a case insensitive
                        // search, force the sorting order to be determined by a lowercase copy of the original value.

                        $sortingProduct = $objProduct;

                        if(($objProduct->getType()->hasVariants() && (int)$objProduct->pid === 0)
                            && in_array($strField, $objProduct->getType()->getVariantAttributes())) {
                            $sortingProduct = Product::findDefaultVariantOfProduct($objProduct);
                        }

                        // Temporary fix for price attribute (see #945)
                        if ('price' === $strField) {
                            if (null !== $sortingProduct->getPrice()) {
                                $arrData[$strField][$objProduct->id] = $sortingProduct->getPrice()->getAmount();
                            } else {
                                $arrData[$strField][$objProduct->id] = 0;
                            }
                        } else {
                            $arrData[$strField][$objProduct->id] = strtolower(
                                str_replace('"', '', $sortingProduct->$strField)
                            );
                        }
                    }

                    $arrParam[] = &$arrData[$strField];
                    $arrParam[] = $arrConfig[0];
                    $arrParam[] = $arrConfig[1];
                }

                // Add product array as the last item.
                // This will sort the products array based on the sorting of the passed in arguments.
                $arrParam[] = &$arrProducts;
                \call_user_func_array('array_multisort', $arrParam);
            }

            $objProducts = new Collection($arrProducts, static::$strTable);
        }

        return $objProducts;
    }

    /**
     * Return select statement to load product data including multilingual fields
     *
     * @param array $arrOptions an array of columns
     *
     * @return string
     */
    protected static function buildFindQuery(array $arrOptions)
    {
        $objBase         = DcaExtractor::getInstance($arrOptions['table']);
        $hasTranslations = (static::countTranslatedProducts() > 0);
        $hasVariants     = (ProductType::countByVariants() > 0);

        $arrJoins  = array();
        $arrFields = array(
            $arrOptions['table'] . '.*',
            "'" . str_replace('-', '_', $GLOBALS['TL_LANGUAGE']) . "' AS language",
        );

        if ($hasVariants) {
            $arrFields[] = sprintf(
                'IF(%s.pid>0, parent.type, %s.type) AS type',
                $arrOptions['table'],
                $arrOptions['table']
            );
        }

        if ($hasTranslations) {
            foreach (Attribute::getMultilingualFields() as $attribute) {
                $arrFields[] = "IFNULL(translation.$attribute, " . $arrOptions['table'] . ".$attribute) AS $attribute";
            }
        }

        foreach (Attribute::getFetchFallbackFields() as $attribute) {
            $arrFields[] = "{$arrOptions['table']}.$attribute AS {$attribute}_fallback";
        }

        if ($hasTranslations) {
            $arrJoins[] = sprintf(
                " LEFT OUTER JOIN %s translation ON %s.id=translation.pid AND translation.language='%s'",
                $arrOptions['table'],
                $arrOptions['table'],
                str_replace('-', '_', $GLOBALS['TL_LANGUAGE'])
            );

            $arrOptions['group'] = (null === $arrOptions['group'] ? '' : $arrOptions['group'].', ') . 'translation.id';
        }

        if ($hasVariants) {
            $arrJoins[] = sprintf(
                ' LEFT OUTER JOIN %s parent ON %s.pid=parent.id',
                $arrOptions['table'],
                $arrOptions['table']
            );
        }

        $arrFields[] = 'GROUP_CONCAT(c.page_id) AS product_categories';
        $arrJoins[] = sprintf(
            ' LEFT OUTER JOIN %s c ON %s=c.pid',
            ProductCategory::getTable(),
            ($hasVariants ? "IFNULL(parent.id, {$arrOptions['table']}.id)" : "{$arrOptions['table']}.id")
        );

        if ('c.sorting' === ($arrOptions['order'] ?? '')) {
            $arrFields[] = 'c.sorting';

            $arrOptions['group'] = (null === $arrOptions['group'] ? '' : $arrOptions['group'].', ') . 'c.id';
        }

        if ($objBase->hasRelations()) {
            $intCount = 0;

            foreach ($objBase->getRelations() as $strKey => $arrConfig) {
                // Automatically join the single-relation records
                if (('eager' === $arrConfig['load'] || ($arrOptions['eager'] ?? false))
                    && ('hasOne' === $arrConfig['type'] || 'belongsTo' === $arrConfig['type'])
                ) {
                    if (\is_array($arrOptions['joinAliases'] ?? null)
                        && ($key = array_search($arrConfig['table'], $arrOptions['joinAliases'], true)) !== false
                    ) {
                        $strJoinAlias = $key;
                        unset($arrOptions['joinAliases'][$key]);
                    } else {
                        ++$intCount;
                        $strJoinAlias = 'j' . $intCount;
                    }

                    $objRelated = DcaExtractor::getInstance($arrConfig['table']);

                    foreach ($objRelated->getFields() as $strField => $config) {
                        $arrFields[] = $strJoinAlias . '.' . $strField . ' AS ' . $strKey . '__' . $strField;
                    }

                    $arrJoins[] = sprintf(
                        ' LEFT JOIN %s %s ON %s.%s=%s.id',
                        $arrConfig['table'],
                        $strJoinAlias,
                        $arrOptions['table'],
                        $strKey,
                        $strJoinAlias
                    );
                }
            }
        }

        // Generate the query
        $strQuery = 'SELECT ' . implode(', ', $arrFields) . ' FROM ' . $arrOptions['table'] . implode('', $arrJoins);

        // Where condition
        if (!\is_array($arrOptions['column'] ?? null)) {
            $arrOptions['column'] = array($arrOptions['table'] . '.' . $arrOptions['column'] . '=?');
        }

        // The model must never find a language record
        $strQuery .= " WHERE {$arrOptions['table']}.language='' AND " . implode(' AND ', $arrOptions['column']);

        // Group by
        if (($arrOptions['group'] ?? null) !== null) {
            $strQuery .= ' GROUP BY ' . $arrOptions['group'];
        }

        // Order by
        if (($arrOptions['order'] ?? null) !== null) {
            $strQuery .= ' ORDER BY ' . $arrOptions['order'];
        }

        return $strQuery;
    }

    /**
     * Build a query based on the given options to count the number of products.
     *
     * @param array $arrOptions The options array
     *
     * @return string
     */
    protected static function buildCountQuery(array $arrOptions)
    {
        $hasTranslations = (static::countTranslatedProducts() > 0);
        $hasVariants     = (ProductType::countByVariants() > 0);

        $arrJoins  = array();
        $arrFields = array(
            $arrOptions['table'] . '.id',
            "'" . str_replace('-', '_', $GLOBALS['TL_LANGUAGE']) . "' AS language",
        );

        if ($hasVariants) {
            $arrFields[] = sprintf(
                'IF(%s.pid>0, parent.type, %s.type) AS type',
                $arrOptions['table'],
                $arrOptions['table']
            );
        }

        if ($hasTranslations) {
            foreach (Attribute::getMultilingualFields() as $attribute) {
                $arrFields[] = "IFNULL(translation.$attribute, " . $arrOptions['table'] . ".$attribute) AS $attribute";
            }
        }

        if ($hasTranslations) {
            $arrJoins[] = sprintf(
                " LEFT OUTER JOIN %s translation ON %s.id=translation.pid AND translation.language='%s'",
                $arrOptions['table'],
                $arrOptions['table'],
                str_replace('-', '_', $GLOBALS['TL_LANGUAGE'])
            );

            $arrOptions['group'] = (null === $arrOptions['group'] ? '' : $arrOptions['group'].', ') . 'translation.id, tl_iso_product.id';
        }

        if ($hasVariants) {
            $arrJoins[] = sprintf(
                ' LEFT OUTER JOIN %s parent ON %s.pid=parent.id',
                $arrOptions['table'],
                $arrOptions['table']
            );
        }

        $arrJoins[] = sprintf(
            ' LEFT OUTER JOIN %s c ON %s=c.pid',
            ProductCategory::getTable(),
            ($hasVariants ? "IFNULL(parent.id, {$arrOptions['table']}.id)" : "{$arrOptions['table']}.id")
        );

        // Generate the query
        $strWhere = '';
        $strQuery = '
            SELECT
                ' . implode(', ', $arrFields) . '
            FROM ' . $arrOptions['table'] . implode('', $arrJoins);

        // Where condition
        if (!empty($arrOptions['column'])) {
            if (!\is_array($arrOptions['column'])) {
                $arrOptions['column'] = array($arrOptions['table'] . '.' . $arrOptions['column'] . '=?');
            }

            $strWhere = ' AND ' . implode(' AND ', $arrOptions['column']);
        }

        // The model must never find a language record
        $strQuery .= " WHERE {$arrOptions['table']}.language=''" . $strWhere;

        // Group by
        if ($arrOptions['group'] !== null) {
            $strQuery .= ' GROUP BY ' . $arrOptions['group'];
        }

        return 'SELECT COUNT(*) AS count FROM ('.$strQuery.') c1';
    }

    /**
     * Return select statement to load product data including multilingual fields
     *
     * @param array $arrOptions     an array of columns
     * @param array $arrJoinAliases an array of table join aliases
     *
     * @return string
     *
     * @deprecated  use buildFindQuery introduced in Contao 3.3
     */
    protected static function buildQueryString($arrOptions, $arrJoinAliases = array('t' => 'tl_iso_producttype'))
    {
        $arrOptions['joinAliases'] = $arrJoinAliases;

        return static::buildFindQuery((array) $arrOptions);
    }
}
