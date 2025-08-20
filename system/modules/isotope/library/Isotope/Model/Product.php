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
use Contao\Model\Collection;
use Contao\System;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\CoreBundle\Model\ProductCategory;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\CollectibleInterface;

/**
 * The basic Isotope product model
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $language
 * @property int    $dateAdded
 * @property int    $type
 * @property array  $inherit
 * @property bool   $fallback
 * @property string $alias
 * @property string $sku
 * @property string $name
 * @property string $teaser
 * @property string $description
 * @property string $meta_title
 * @property string $meta_description
 * @property bool   $shipping_exempt
 * @property array  $images
 * @property bool   $protected
 * @property array  $groups
 * @property array  $cssID
 * @property bool   $published
 * @property string $start
 * @property string $stop
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
    protected static $strInterface = Product::class;

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
     * Find all published products
     *
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
        if (!\Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
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
     *
     * @return Collection|Product[]|null
     */
    public static function findPublishedByCategories(array $arrCategories, array $arrOptions = array())
    {
        return static::findPublishedBy(
            array('c.pid IN (' . implode(',', array_map('intval', $arrCategories)) . ')'),
            null,
            $arrOptions
        );
    }

    /**
     * Find a single frontend-available product by primary key
     *
     * @param int   $intId
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
     * Find variant of a product based on attribute values.
     * The given variant IDs must be published and available to the current user.
     */
    public static function findVariantOfProduct(array $variantIds, array $attributes, array $arrOptions = array()): self|null
    {
        $t = static::$strTable;

        $arrColumns = array(
            "$t.id IN (" . implode(',', $variantIds) . ')',
            "$t." . implode("=? AND $t.", array_keys($attributes)) . '=?'
        );

        $arrOptions = array_merge(
            array(
                 'limit'  => 1,
                 'column' => $arrColumns,
                 'value'  => $attributes,
                 'return' => 'Model'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Finds the default variant of a product.
     *
     * @return static|null
     */
    public static function findDefaultVariantOfProduct(int $pid, array $variantIds, array $arrOptions = array())
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

        $defaultId = $cache[$pid] ?? null;

        if ($defaultId < 1 || !\in_array($defaultId, $variantIds)) {
            return null;
        }

        return static::findByPk($defaultId, $arrOptions);
    }

    /**
     * Returns the number of published products.
     *
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
        if (!\Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
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
     *
     * @return Product|Product[]|Collection|null
     */
    protected static function find(array $arrOptions)
    {
        $arrOptions['group'] = static::getTable() . '.id' . (null === ($arrOptions['group'] ?? null) ? '' : ', '.$arrOptions['group']);

        return parent::find($arrOptions);
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

        /** @var AttributeRegistry $attributeRegistry */
        $attributeRegistry = System::getContainer()->get(AttributeRegistry::class);

        if ($hasTranslations) {
            foreach ($attributeRegistry->getTranslatableFields() as $attribute) {
                $arrFields[] = "IFNULL(translation.$attribute, " . $arrOptions['table'] . ".$attribute) AS $attribute";
            }
        }

        foreach ($attributeRegistry->getFetchTranslationFallbackFields() as $attribute) {
            $arrFields[] = "{$arrOptions['table']}.$attribute AS {$attribute}_fallback";
        }

        if ($hasTranslations) {
            $arrJoins[] = sprintf(
                " LEFT OUTER JOIN %s translation ON %s.id=translation.pid AND translation.language='%s'",
                $arrOptions['table'],
                $arrOptions['table'],
                str_replace('-', '_', $GLOBALS['TL_LANGUAGE'])
            );

            $arrOptions['group'] = (empty($arrOptions['group']) ? '' : $arrOptions['group'].', ') . 'translation.id';
        }

        if ($hasVariants) {
            $arrJoins[] = sprintf(
                ' LEFT OUTER JOIN %s parent ON %s.pid=parent.id',
                $arrOptions['table'],
                $arrOptions['table']
            );
        }

        $arrFields[] = 'GROUP_CONCAT(c.pid) AS product_categories';
        $arrJoins[] = sprintf(
            ' LEFT OUTER JOIN %s c ON %s=c.product_id',
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
        $strQuery .= " WHERE {$arrOptions['table']}.language=''";

        if (!empty($arrOptions['column'])) {
            $strQuery .= ' AND '. implode(' AND ', $arrOptions['column']);
        }

        // Group by
        if (($arrOptions['group'] ?? null) !== null) {
            $strQuery .= ' GROUP BY ' . $arrOptions['group'];
        }

        if (isset($arrOptions['having'])) {
            $strQuery .= " HAVING " . $arrOptions['having'];
        }

        // Order by
        if (($arrOptions['order'] ?? null) !== null) {
            $strQuery .= ' ORDER BY ' . $arrOptions['order'] . ', c.product_sorting, c.id';
        } else {
            $strQuery .= ' ORDER BY c.product_sorting, c.id';
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

            $arrOptions['group'] = (!empty($arrOptions['group']) ? $arrOptions['group'].', ' : '') . 'translation.id, tl_iso_product.id';
        }

        if ($hasVariants) {
            $arrJoins[] = sprintf(
                ' LEFT OUTER JOIN %s parent ON %s.pid=parent.id',
                $arrOptions['table'],
                $arrOptions['table']
            );
        }

        $arrJoins[] = sprintf(
            ' LEFT OUTER JOIN %s c ON %s=c.product_id',
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
