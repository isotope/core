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

use Haste\Input\Input;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\RelatedProduct;
use Isotope\RequestCache\Sort;

/**
 * Class ModuleIsotopeRelatedProducts
 *
 * @property array $iso_related_categories
 */
class RelatedProducts extends ProductList
{

    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: RELATED PRODUCTS ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (!Input::getAutoItem('product', false, true)) {
            return '';
        }

        $this->iso_related_categories = deserialize($this->iso_related_categories);

        if (!is_array($this->iso_related_categories) || 0 === count($this->iso_related_categories)) {
            return '';
        }

        // Prevent hiding the list which is not supported in this module (see ProductList::generate())
        $this->iso_hide_list = false;

        return parent::generate();
    }


    /**
     * Find all products we need to list.
     * @param   array|null
     * @return  array
     */
    protected function findProducts($arrCacheIds = null)
    {
        $productIds     = [];
        $currentProduct = Product::findAvailableByIdOrAlias(Input::getAutoItem('product', false, true));

        if (null === $currentProduct) {
            return [];
        }

        /** @var RelatedProduct[] $relatedProducts */
        $relatedProducts = RelatedProduct::findByProductAndCategories($currentProduct, $this->iso_related_categories);

        if (null !== $relatedProducts) {
            foreach ($relatedProducts as $category) {
                $ids = trimsplit(',', $category->products);

                if (is_array($ids) && 0 !== count($ids)) {
                    $productIds = array_unique(array_merge($productIds, $ids));
                }
            }
        }

        if (0 === count($productIds)) {
            return [];
        }

        $columns = [Product::getTable() . '.id IN (' . implode(',', array_map('intval', $productIds)) . ')'];
        $options = ['order' => \Database::getInstance()->findInSet(Product::getTable() . '.id', $productIds)];

        // Apply new/old product filter
        if ('show_new' === $this->iso_newFilter) {
            $columns[] = Product::getTable() . '.dateAdded>=' . Isotope::getConfig()->getNewProductLimit();
        } elseif ('show_old' === $this->iso_newFilter) {
            $columns[] = Product::getTable() . '.dateAdded<' . Isotope::getConfig()->getNewProductLimit();
        }

        if ($this->iso_list_where != '') {
            $columns[] = $this->iso_list_where;
        }

        if ($this->iso_listingSortField != '') {
            $direction = 'DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending();
            $options['sorting'] = [$this->iso_listingSortField => $direction];
        }

        $objProducts = Product::findAvailableBy($columns, [], $options);

        return (null === $objProducts) ? [] : $objProducts->getModels();
    }

    /**
     * {@inheritdoc}
     */
    protected function compileEmptyMessage($disableSearchIndex = true)
    {
        parent::compileEmptyMessage(false);
    }

    /**
     * @inheritdoc
     */
    protected function getCacheKey()
    {
        return md5(
            'relatedproducts=' . $this->id . ':'
            . 'product=' . Input::getAutoItem('product', false, true)
        );
    }
}
