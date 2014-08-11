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
 * IsotopeShipping interface defines an Isotope shipping method
 */
interface IsotopeShipping
{

    /**
     * Return boolean flag if the shipping method is available
     * @return  bool
     */
    public function isAvailable();

    /**
     * Return true if shipping price is not a fixed amount
     * @return  bool
     */
    public function isPercentage();

    /**
     * Get the percentage amount (if applicable)
     * @return  float
     */
    public function getPercentage();

    /**
     * Return label for the shipping method
     * @return  string
     */
    public function getLabel();

    /**
     * Return percentage label if price is percentage
     * @return  string
     */
    public function getPercentageLabel();

    /**
     * Return the calculated total price for shipping
     * @return  float
     */
    public function getPrice();

    /**
     * Return information or advanced features in the backend.
     * Use this function to present advanced features or basic shipping information for an order in the backend.
     * @param integer
     * @return string
     */
    public function backendInterface($orderId);

    /**
     * Return the checkout review information.
     *
     * Use this to return custom checkout information about this shipping module.
     * Example: Information about tracking codes.
     * @return string
     */
    public function checkoutReview();

    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge(IsotopeProductCollection $objCollection);
}
