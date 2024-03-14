<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Controller;
use Contao\Database;
use Contao\Date;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\Isotope;
use Isotope\Model\ProductType;

/**
 * AbstractProductFilter provides basic methods to handle product filtering
 *
 * @property array  $iso_searchFields
 * @property string $iso_searchAutocomplete
 * @property array  $iso_filterFields
 * @property bool   $iso_filterHideSingle
 * @property string $iso_newFilter
 * @property array  $iso_sortingFields
 * @property string $iso_listingSortField
 * @property string $iso_listingSortDirection
 * @property bool   $iso_enableLimit
 * @property int    $iso_perPage
 * @property string $iso_filterTpl
 */
abstract class AbstractProductFilter extends Module
{
    public const FILTER_NEW = 'show_new';
    public const FILTER_OLD = 'show_old';

    /**
     * Constructor.
     *
     * @param ModuleModel|object $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');
    }

    /**
     * @inheritdoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_filterFields';
        $props[] = 'iso_sortingFields';
        $props[] = 'iso_searchFields';

        return $props;
    }

    /**
     * Returns an array of attribute values found in the product table
     *
     * @param string $attribute
     * @param string $newFilter
     * @param string $sqlWhere
     * @return array
     */
    protected function getUsedValuesForAttribute($attribute, array $categories, $newFilter = '', $sqlWhere = '')
    {
        $attributeTypes = $this->getProductTypeIdsByAttribute($attribute);
        $variantTypes   = $this->getProductTypeIdsByAttribute($attribute, true);
        $atypeCount     = \count($attributeTypes);
        $vtypeCount     = \count($variantTypes);

        if (0 === $atypeCount && 0 === $vtypeCount) {
            return array();
        }

        $values         = array();
        $typeConditions = array();
        $join           = '';
        $categoryWhere  = '';
        $published      = '';
        $time           = Date::floorToMinute();
        $isPreviewModel = \Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode();

        if ('' !== (string) $sqlWhere) {
            $sqlWhere = ' AND ' . $sqlWhere;
        }

        // Apply new/old product filter
        if (self::FILTER_NEW === $newFilter) {
            $sqlWhere .= ' AND tl_iso_product.dateAdded>=' . Isotope::getConfig()->getNewProductLimit();
        } elseif (self::FILTER_OLD === $newFilter) {
            $sqlWhere .= ' AND tl_iso_product.dateAdded<' . Isotope::getConfig()->getNewProductLimit();
        }

        if (!$isPreviewModel) {
            $published = "
                AND tl_iso_product.published='1'
                AND (tl_iso_product.start='' OR tl_iso_product.start<'$time')
                AND (tl_iso_product.stop='' OR tl_iso_product.stop>'" . ($time + 60) . "')
            ";
        }

        if (0 !== $atypeCount) {
            $typeConditions[] = 'tl_iso_product.type IN (' . implode(',', $attributeTypes) . ')';
        }

        if (0 !== $vtypeCount) {
            $typeConditions[] = 'translation.type IN (' . implode(',', $variantTypes) . ')';
            $join             = 'LEFT OUTER JOIN tl_iso_product translation ON tl_iso_product.pid=translation.id';
            $categoryWhere    = 'OR tl_iso_product.pid IN (
                                    SELECT pid
                                    FROM tl_iso_product_category
                                    WHERE page_id IN (' . implode(',', $categories) . ')
                                )';

            if (!$isPreviewModel) {
                $published .= " AND (
                    tl_iso_product.pid=0 OR (
                        translation.published='1'
                        AND (translation.start='' OR translation.start<'$time')
                        AND (translation.stop='' OR translation.stop>'" . ($time + 60) . "')
                    )
                )";
            }
        }

        $result = Database::getInstance()->execute("
            SELECT DISTINCT tl_iso_product.$attribute AS options
            FROM tl_iso_product
            $join
            WHERE
                tl_iso_product.language=''
                AND tl_iso_product.$attribute!=''
                " . $published . '
                AND (
                    tl_iso_product.id IN (
                        SELECT pid
                        FROM tl_iso_product_category
                        WHERE page_id IN (' . implode(',', $categories) . ")
                    )
                    $categoryWhere
                )
                AND (
                    " . implode(' OR ', $typeConditions) . "
                )
                $sqlWhere
        ");

        while ($result->next()) {
            if ($this->isCsv($attribute)) {
                $values = array_merge($values, explode(',', $result->options));
            } else {
                $values = array_merge($values, StringUtil::deserialize($result->options, true));
            }
        }

        return array_unique($values);
    }

    /**
     * Get the sorting labels (asc/desc) for an attribute
     *
     * @param string
     *
     * @return array
     */
    protected function getSortingLabels($field)
    {
        $arrData = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field];

        switch ($arrData['eval']['rgxp'] ?? null) {
            case 'price':
            case 'digit':
                return array($GLOBALS['TL_LANG']['MSC']['low_to_high'], $GLOBALS['TL_LANG']['MSC']['high_to_low']);

            case 'date':
            case 'time':
            case 'datim':
                return array($GLOBALS['TL_LANG']['MSC']['old_to_new'], $GLOBALS['TL_LANG']['MSC']['new_to_old']);
        }

        return array($GLOBALS['TL_LANG']['MSC']['a_to_z'], $GLOBALS['TL_LANG']['MSC']['z_to_a']);
    }

    /**
     * Returns true if the attribute is multiple choice.
     *
     * @param string $attribute
     *
     * @return bool
     */
    protected function isMultiple($attribute)
    {
        return (bool) ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['multiple'] ?? false);
    }

    /**
     * Returns true if the attribute contains CSV values.
     *
     * @param string $attribute
     *
     * @return bool
     */
    protected function isCsv($attribute)
    {
        return $this->isMultiple($attribute)
            && ',' === $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['csv'];
    }

    /**
     * Get product type IDs with given attribute enabled
     *
     * @param string $attributeName
     * @param bool   $forVariants
     *
     * @return array
     */
    private function getProductTypeIdsByAttribute($attributeName, $forVariants = false)
    {
        static $cache;

        if (null === $cache) {
            /** @var ProductType[] $productTypes */
            $productTypes = ProductType::findAll();
            $cache        = array();

            if (null !== $productTypes) {
                foreach ($productTypes as $type) {
                    foreach ($type->attributes as $attribute => $config) {
                        if ($config['enabled'] ?? false) {
                            $cache['attributes'][$attribute][] = $type->id;
                        }
                    }

                    if ($type->variants) {
                        foreach ($type->variant_attributes as $attribute => $config) {
                            if ($config['enabled'] ?? false) {
                                $cache['variant_attributes'][$attribute][] = $type->id;
                            }
                        }
                    }
                }
            }
        }

        if ($forVariants) {
            return (array) ($cache['variant_attributes'][$attributeName] ?? null);
        }

        return (array) ($cache['attributes'][$attributeName] ?? null);
    }
}
