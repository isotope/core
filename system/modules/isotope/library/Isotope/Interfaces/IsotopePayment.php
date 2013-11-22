<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;


/**
 * IsotopePayment interface describes an Isotope payment method
 */
interface IsotopePayment
{
    /**
     * Process checkout payment. Must be implemented in each payment module
     * @access public
     * @return mixed
     */
    public function processPayment();

    /**
     * Return a html form for checkout or false
     * @return mixed
     */
    public function checkoutForm();

    /**
     * Return information or advanced features in the backend.
     *
     * Use this function to present advanced features or basic payment information for an order in the backend.
     * @param integer Order ID
     * @return string
     */
    public function backendInterface($orderId);

    /**
     * Return the checkout review information.
     *
     * Use this to return custom checkout information about this payment module.
     * Example: parial information about the used credit card.
     *
     * @return string
     */
    public function checkoutReview();

    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge($objCollection);
}
