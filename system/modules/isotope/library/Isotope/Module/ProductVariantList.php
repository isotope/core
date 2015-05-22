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

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductCategory;
use Isotope\Model\ProductType;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\RequestCache\Sort;


/**
 * Class ProductVariantList
 *
 * Front end module Isotope "product variant list".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class ProductVariantList extends ProductList
{

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT VARIANT LIST ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    /**
     * Fill the object's arrProducts array
     * @param   array|null
     * @return  array
     */
    protected function findProducts($arrCacheIds = null)
    {
        $t             = Product::getTable();
        $arrColumns    = array();
        $arrCategories = $this->findCategories();
        $arrProductIds = \Database::getInstance()->query("SELECT pid FROM " . ProductCategory::getTable() . " WHERE page_id IN (" . implode(',', $arrCategories) . ")")->fetchEach('pid');
        $arrTypes = \Database::getInstance()->query("SELECT id FROM " . ProductType::getTable() . " WHERE variants='1'")->fetchEach('id');

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

        if (!empty($arrCacheIds) && is_array($arrCacheIds)) {
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
