<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Product;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductCategory;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductType;
use Model\QueryBuilder;

/**
 * AbstractProduct implements basic methods of product interface based on Model data.
 */
abstract class AbstractProduct extends Product
{
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
        try {
            return $this->getRelated('type');
        } catch (\Exception $e) {
            return null;
        }
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
            && is_array($GLOBALS['ISO_HOOKS']['productIsAvailable'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['productIsAvailable'] as $callback) {
                $available = \System::importStatic($callback[0])->{$callback[1]}($this, $objCollection);

                // If return value is boolean then we accept it as result
                if (true === $available || false === $available) {
                    return $available;
                }
            }
        }

        if (BE_USER_LOGGED_IN !== true && !$this->isPublished()) {
            return false;
        }

        $member = $objCollection->getMember();

        // Show to guests only
        if ($this->guests
            && null !== $member
            && BE_USER_LOGGED_IN !== true
            && !$this->protected
        ) {
            return false;
        }

        // Protected product
        if (BE_USER_LOGGED_IN !== true && $this->protected) {
            if (null === $member) {
                return false;
            }

            $groups       = deserialize($this->groups);
            $memberGroups = deserialize($member->groups);

            if (!is_array($groups)
                || empty($groups)
                || !is_array($memberGroups)
                || empty($memberGroups)
                || !count(array_intersect($groups, $memberGroups))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isPublished()
    {
        $time = \Date::floorToMinute();

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

        return null !== $this->getType() && $this->getType()->shipping_exempt;
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
        try {
            /** @var ProductType $type */
            $type = $this->getRelated('type');
        } catch (\Exception $e) {
            return false;
        }

        return $type->hasVariants();
    }

    /**
     * @inheritdoc
     */
    public function hasVariantPrices()
    {
        return $this->hasVariants() && in_array('price', $this->getVariantAttributes(), true);
    }

    /**
     * @inheritdoc
     */
    public function hasAdvancedPrices()
    {
        /** @var ProductType $objType */
        $objType = $this->getRelated('type');

        return $objType->hasAdvancedPrices();
    }

    /**
     * @inheritdoc
     */
    public function getCategories($blnPublished = false)
    {
        $key = ($blnPublished ? 'published' : 'all');

        if (null === $this->arrCategories || !isset($this->arrCategories[$key])) {
            if ($blnPublished) {
                $options          = ProductCategory::getFindByPidForPublishedPagesOptions($this->getProductId());
                $options['table'] = ProductCategory::getTable();
                $query            = QueryBuilder::find($options);
                $values           = (array) $options['value'];
            } else {
                $query  = 'SELECT page_id FROM tl_iso_product_category WHERE pid=?';
                $values = array($this->getProductId());
            }

            $objCategories = \Database::getInstance()->prepare($query)->execute($values);

            $this->arrCategories[$key] = $objCategories->fetchEach('page_id');

            // Sort categories by the backend drag&drop
            $arrOrder = deserialize($this->orderPages);
            if (!empty($arrOrder) && is_array($arrOrder)) {
                $this->arrCategories[$key] = array_unique(
                    array_merge(
                        array_intersect(
                            $arrOrder,
                            $this->arrCategories[$key]
                        ),
                        $this->arrCategories[$key]
                    )
                );
            }
        }

        return $this->arrCategories[$key];
    }

    /**
     * @inheritDoc
     */
    public function setRow(array $arrData)
    {
        $this->arrCategories = null;

        return parent::setRow($arrData);
    }
}
