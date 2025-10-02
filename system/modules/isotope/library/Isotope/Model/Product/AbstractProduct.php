<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Product;

use Contao\Date;
use Contao\System;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Product;

/**
 * AbstractProduct implements basic methods of product interface based on Model data.
 */
abstract class AbstractProduct extends Product
{
    /**
     * @var ProductType|null
     */
    private $objType = false;

    /**
     * Assigned categories (pages)
     * @var array
     */
    protected $arrCategories;

    /**
     * @inheritdoc
     *
     * @return ProductType|null
     */
    public function getType()
    {
        if (false === $this->objType) {
            try {
                $this->objType = $this->getRelated('type');
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->objType;
    }

    /**
     * @inheritdoc
     */
    public function isVariant()
    {
        return ($this->pid > 0 && $this->hasVariants());
    }

    /**
     * @inheritdoc
     */
    public function hasVariants()
    {
        $type = $this->getType();

        return null !== $type && $type->hasVariants();
    }

    /**
     * @inheritdoc
     */
    public function hasVariantPrices()
    {
        return $this->hasVariants() && \in_array('price', $this->getType()->getVariantAttributes(), true);
    }

    /**
     * @inheritdoc
     */
    public function hasAdvancedPrices()
    {
        $type = $this->getType();

        return null !== $type && $type->hasAdvancedPrices();
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    public function getCategories($blnPublished = false)
    {
        /** @var ProductCategoryUtil $categories */
        $categories = System::getContainer()->get(ProductCategoryUtil::class);

        return $blnPublished ? $categories->getPublishedForProduct($this) : $categories->getForProduct($this);
    }
}
