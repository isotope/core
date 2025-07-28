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

use Isotope\Interfaces\IsotopeProduct;
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
        $arrColumns    = array();
        $arrFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules);
        $arrCategories = $this->findCategories($arrFilters);
        $queryBuilder = new FilterQueryBuilder($arrFilters);

        $arrColumns[] = "(
            (tl_iso_product.pid=0 AND tl_iso_product.type NOT IN (SELECT id FROM tl_iso_producttype WHERE variants='1'))
            OR tl_iso_product.pid>0
        )";

        if (1 === \count($arrCategories)) {
            $arrColumns[] = "c.pid=" . $arrCategories[0];
        } else {
            $arrColumns[] = "c.pid IN (" . implode(',', $arrCategories) . ")";
        }

        if (!empty($arrCacheIds) && \is_array($arrCacheIds)) {
            $arrColumns[] = Product::getTable() . ".id IN (" . implode(',', $arrCacheIds) . ")";
        }

        if ($queryBuilder->hasSqlCondition()) {
            $arrColumns[] = $queryBuilder->getSqlWhere();
        }

        $arrSorting = Isotope::getRequestCache()->getSortingsForModules($this->iso_filterModules);

        if (empty($arrSorting) && $this->iso_listingSortField != '') {
            $direction = ('DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending());
            $arrSorting[$this->iso_listingSortField] = $direction;
        }

        $objProducts = Product::findAvailableBy(
            $arrColumns,
            $queryBuilder->getSqlValues(),
            array(
                 'order'   => 1 === \count($arrCategories) ? 'c.sorting' : null,
                 'filters' => $queryBuilder->getFilters(),
                 'sorting' => $arrSorting,
            )
        );

        return (null === $objProducts) ? array() : $objProducts->getModels();
    }

    protected function getProductConfig(IsotopeProduct $product)
    {
        $config = parent::getProductConfig($product);

        $config['loadFallback'] = false;

        return $config;
    }
}
