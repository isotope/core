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

use Isotope\Interfaces\IsotopeProductCollection;


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
     * Process post-sale requests. Does nothing by default.
     *
     * This function can be called from the postsale.php file when the shipping server is requestion/posting a status change.
     * You can see an implementation example in PaymentPostfinance.php
     */
    public function processPostsale();

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
