<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;


/**
 * IsotopeShipping interface defines an Isotope shipping method
 */
interface IsotopeShipping
{

    /**
     * Return the name and description for this shipping method
     * @return array
     */
    public static function getClassLabel();

    /**
     * Return information or advanced features in the backend.
     * Use this function to present advanced features or basic shipping information for an order in the backend.
     * @param integer
     * @return string
     */
    public function backendInterface($orderId);

    /**
     * Process post-sale requests. Does nothing by default.
     *
     * This function can be called from the postsale.php file when the shipping server is requestion/posting a status change.
     * You can see an implementation example in PaymentPostfinance.php
     */
    public function processPostSale();

    /**
     * This function is used to gather any addition shipping options that might be available specific to the current customer or order.
     * For example, expedited shipping based on customer location.
     * @param object
     * @return string
     */
    public function getShippingOptions(&$objModule);

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
    public function getSurcharge($objCollection);
}
