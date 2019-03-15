<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
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
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_related_categories';

        return $props;
    }

    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        if (!Input::getAutoItem('product', false, true)) {
            return '';
        }

        if (0 === count($this->iso_related_categories)) {
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

        if ($currentProduct instanceof Product\Standard) {
            $currentProduct = $currentProduct->validateVariant();
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
