<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

use Contao\PageModel;
use Isotope\Model\ProductType;


/**
 * IsotopeProduct is the interface for a product object
 */
interface IsotopeProduct
{
    /**
     * Returns the ID for the product or variant.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the ID for this product or the parent of a variant.
     *
     * @return int
     */
    public function getProductId();

    /**
     * Return the unique form ID for the product
     *
     * @return string
     */
    public function getFormId();

    /**
     * Returns the product type for this product, or null if not applicable or not found.
     *
     * @return ProductType|null
     */
    public function getType();

    /**
     * Returns the product name, necessary to store as fallback in the product collection.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the product SKU, necessary to store as fallback in the product collection.
     *
     * @return string
     */
    public function getSku();

    /**
     * Returns true if the product is available in the frontend
     *
     * @return bool
     */
    public function isAvailableInFrontend();

    /**
     * Returns true if the product is available in the given collection
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return bool
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection);

    /**
     * Returns true if the product is published, otherwise returns false
     *
     * @return bool
     */
    public function isPublished();

    /**
     * Checks whether a product is new according to the current store config
     *
     * @return bool
     */
    public function isNew();

    /**
     * Checks whether a product is exempt from shipping
     *
     * @return bool
     */
    public function isExemptFromShipping();

    /**
     * Returns true if variants are enabled in the product, otherwise returns false
     *
     * @return bool
     */
    public function hasVariants();

    /**
     * Returns an array of variant IDs
     *
     * @return array
     */
    public function getVariantIds();

    /**
     * Returns true if this product is a variant
     *
     * @return bool
     */
    public function isVariant();

    /**
     * Returns true if product has variants, and the price is a variant attribute
     *
     * @return bool
     */
    public function hasVariantPrices();

    /**
     * Returns true if advanced prices are enabled in the product type, otherwise returns false
     *
     * @return bool
     */
    public function hasAdvancedPrices();

    /**
     * Get product price model
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return IsotopePrice
     */
    public function getPrice(IsotopeProductCollection $objCollection = null);

    /**
     * Return minimum quantity for the product (from advanced price tiers)
     *
     * @return int
     */
    public function getMinimumQuantity();

    /**
     * See IsotopeProductWithOptions interface!
     *
     * @return array
     */
    public function getOptions();

    /**
     * Generate a product template
     *
     * @param array $arrConfig
     *
     * @return string
     */
    public function generate(array $arrConfig);

    /**
     * Returns URL with product alias to given page.
     *
     * @return string
     */
    public function generateUrl(PageModel $objJumpTo = null/*, bool $absolute = false*/);
}
