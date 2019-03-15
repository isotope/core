<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * @copyright  Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\RequestCache\Sort;

/**
 * Frontend module to show a list of product variants.
 */
class ProductVariantList extends ProductList
{
    /**
     * Fill the object's arrProducts array
     *
     * @param array|null $arrCacheIds
     *
     * @return array
     */
    protected function findProducts($arrCacheIds = null)
    {
        $t             = Product::getTable();
        $arrColumns    = array();
        $arrCategories = $this->findCategories();

        $arrProductIds = \Database::getInstance()
            ->query("
                SELECT pid
                FROM tl_iso_product_category
                WHERE page_id IN (" . implode(',', $arrCategories) . ")
            ")
            ->fetchEach('pid')
        ;

        $arrTypes = \Database::getInstance()
            ->query("SELECT id FROM tl_iso_producttype WHERE variants='1'")
            ->fetchEach('id')
        ;

        if (empty($arrProductIds)) {
            return array();
        }

        $queryBuilder = new FilterQueryBuilder(
            Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules)
        );

        $arrColumns[] = "(
            ($t.id IN (" . implode(',', $arrProductIds) . ") AND $t.type NOT IN (" . implode(',', $arrTypes) . "))
            OR $t.pid IN (" . implode(',', $arrProductIds) . ")
        )";

        if (!empty($arrCacheIds) && \is_array($arrCacheIds)) {
            $arrColumns[] = Product::getTable() . ".id IN (" . implode(',', $arrCacheIds) . ")";
        }

        // Apply new/old product filter
        if ($this->iso_newFilter == 'show_new') {
            $arrColumns[] = Product::getTable() . ".dateAdded>=" . Isotope::getConfig()->getNewProductLimit();
        } elseif ($this->iso_newFilter == 'show_old') {
            $arrColumns[] = Product::getTable() . ".dateAdded<" . Isotope::getConfig()->getNewProductLimit();
        }

        if ($this->iso_list_where != '') {
            $arrColumns[] = $this->iso_list_where;
        }

        if ($queryBuilder->hasSqlCondition()) {
            $arrColumns[] = $queryBuilder->getSqlWhere();
        }

        $arrSorting = Isotope::getRequestCache()->getSortingsForModules($this->iso_filterModules);

        if (empty($arrSorting) && $this->iso_listingSortField != '') {
            $direction = ($this->iso_listingSortDirection == 'DESC' ? Sort::descending() : Sort::ascending());
            $arrSorting[$this->iso_listingSortField] = $direction;
        }

        $objProducts = Product::findAvailableBy(
            $arrColumns,
            $queryBuilder->getSqlValues(),
            array(
                 'order'   => 'c.sorting',
                 'filters' => $queryBuilder->getFilters(),
                 'sorting' => $arrSorting,
            )
        );

        return (null === $objProducts) ? array() : $objProducts->getModels();
    }
}
