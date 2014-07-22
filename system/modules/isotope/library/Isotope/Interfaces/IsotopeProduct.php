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

namespace Isotope\Interfaces;


/**
 * IsotopeProduct is the interface for a product object
 */
interface IsotopeProduct
{

    /**
     * Get the product id (variant INDEPENDENT id)
     * @return  int
     */
    public function getProductId();

    /**
     * Return the unique form ID for the product
     * @return  string
     */
    public function getFormId();

    /**
     * Returns true if the product is available in the frontend
     * @return bool
     */
    public function isAvailableInFrontend();

    /**
     * Returns true if the product is available in the given collection
     * @return bool
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection);

    /**
     * Returns true if the product is published, otherwise returns false
     * @bool
     */
    public function isPublished();

    /**
     * Checks whether a product is new according to the current store config
     * @return boolean
     */
    public function isNew();

    /**
     * Checks whether a product is exempt from shipping
     * @return boolean
     */
    public function isExemptFromShipping();

    /**
     * Returns true if variants are enabled in the product, otherwise returns false
     * @return bool
     */
    public function hasVariants();

    /**
     * Returns true if this product is a variant
     * @return bool
     */
    public function isVariant();

    /**
     * Returns true if product has variants, and the price is a variant attribute
     * @return bool
     */
    public function hasVariantPrices();

    /**
     * Returns true if advanced prices are enabled in the product type, otherwise returns false
     * @return bool
     */
    public function hasAdvancedPrices();

    /**
     * Get product price model
     * @param   IsotopeProductCollection
     * @return  IsotopePrice
     */
    public function getPrice(IsotopeProductCollection $objCollection = null);

    /**
     * Return minimum quantity for the product (from advanced price tiers)
     * @return  int
     */
    public function getMinimumQuantity();

    /**
     * Return the product's options
     * @return  array
     * @deprecated implement getConfiguration
     */
    public function getOptions();

    /**
     * Return the product's options
     * @return  array
     * @todo add in Isotope 3.0
     */
    //public function getConfiguration();

    /**
     * Generate a product template
     * @param   array
     * @return  string
     */
    public function generate(array $arrConfig);

    /**
     * Return the unique form ID for the product
     * @param  \PageModel
     * @return string
     */
    public function generateUrl(\PageModel $objJumpTo = null);
}
