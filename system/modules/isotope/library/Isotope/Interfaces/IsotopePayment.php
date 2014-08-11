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
 * IsotopePayment interface describes an Isotope payment method
 */
interface IsotopePayment
{

    /**
     * Return boolean flag if the payment method is available
     * @return bool
     */
    public function isAvailable();

    /**
     * Return true if payment price is not a fixed amount
     * @return bool
     */
    public function isPercentage();

    /**
     * Get the percentage amount (if applicable)
     * @return float
     */
    public function getPercentage();

    /**
     * Return label for the payment method
     * @return string
     */
    public function getLabel();

    /**
     * Return the calculated total price for payment
     * @return float
     */
    public function getPrice();

    /**
     * Return percentage label if price is percentage
     * @return string
     */
    public function getPercentageLabel();

    /**
     * Process payment on checkout confirmation page.
     * @param   IsotopeProductCollection    $objOrder   The order being places
     * @param   \Module                     $objModule  The checkout module instance
     * @return  mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule);

    /**
     * Return a html form for checkout or false
     * @param   IsotopeProductCollection    $objOrder   The order being places
     * @param   \Module                     $objModule  The checkout module instance
     * @return  mixed
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule);

    /**
     * Return information or advanced features in the backend.
     *
     * Use this function to present advanced features or basic payment information for an order in the backend.
     * @param integer $orderId Order ID
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
