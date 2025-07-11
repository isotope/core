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

use Contao\Database;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Isotope\Haste\Input\Input;
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
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return (int) $this->pid ?: $this->id;
    }

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Returns true if the product is available to show on the website
     *
     * @return bool
     */
    public function isAvailableInFrontend()
    {
        $objCollection = Isotope::getCart();

        if (null === $objCollection) {
            return false;
        }

        return $this->isAvailableForCollection($objCollection);
    }

    /**
     * @inheritdoc
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection)
    {
        if ($objCollection->isLocked()) {
            return true;
        }

        if (isset($GLOBALS['ISO_HOOKS']['productIsAvailable'])
            && \is_array($GLOBALS['ISO_HOOKS']['productIsAvailable'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['productIsAvailable'] as $callback) {
                $available = System::importStatic($callback[0])->{$callback[1]}($this, $objCollection);

                // If return value is boolean then we accept it as result
                if (true === $available || false === $available) {
                    return $available;
                }
            }
        }

        if (!\Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode() && !$this->isPublished()) {
            return false;
        }

        if (
            \in_array('protected', $this->getType()->getAttributes(), true)
            && $this->protected
            && !CompatibilityHelper::isGranted($objCollection->getMember(), $this->groups)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isPublished()
    {
        $time = Date::floorToMinute();

        if (!$this->published) {
            return false;
        } elseif ($this->start != '' && $this->start > $time) {
            return false;
        } elseif ($this->stop != '' && $this->stop < ($time + 60)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether a product is new according to the current store config
     *
     * @return bool
     */
    public function isNew()
    {
        return $this->dateAdded >= Isotope::getConfig()->getNewProductLimit();
    }

    /**
     * @inheritdoc
     */
    public function isExemptFromShipping()
    {
        if ($this->shipping_exempt) {
            return true;
        }

        return null !== $this->getType() && $this->getType()->shipping_exempt === '1';
    }

    /**
     * @inheritdoc
     */
    public function isPickupOnly()
    {
        if ($this->shipping_pickup) {
            return true;
        }

        return null !== $this->getType() && $this->getType()->shipping_exempt === '2';
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

    /**
     * Gets the CSS ID for this product
     *
     * @return string|null
     */
    public function getCssId()
    {
        $css = StringUtil::deserialize($this->cssID, true);

        return ($css[0] ?? null) ? ' id="' . $css[0] . '"' : null;
    }

    /**
     * Gets the CSS classes for this product
     *
     * @return string
     */
    public function getCssClass()
    {
        $classes = ['product'];

        if ($this->isNew()) {
            $classes[] = 'new';
        }

        $arrCSS = StringUtil::deserialize($this->cssID, true);
        if (!empty($arrCSS[1])) {
            $classes[] = (string) $arrCSS[1];
        }

        if (null !== ($type = $this->getType()) && !empty($type->cssClass)) {
            $classes[] = $type->cssClass;
        }

        if ($this->alias === Input::getAutoItem('product', false, true)) {
            $classes[] = 'active';
        }

        return implode(' ', $classes);
    }
}
