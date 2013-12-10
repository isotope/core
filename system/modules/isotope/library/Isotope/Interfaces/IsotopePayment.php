<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule);

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
