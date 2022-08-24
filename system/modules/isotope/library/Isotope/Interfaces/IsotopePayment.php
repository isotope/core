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

use Isotope\Model\ProductCollectionSurcharge\Payment;

/**
 * IsotopePayment interface describes an Isotope payment method
 */
interface IsotopePayment
{
    /**
     * Returns the ID of this payment method.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns whether the payment method is available.
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Return true if payment price is not a fixed amount.
     *
     * @return bool
     */
    public function isPercentage();

    /**
     * Get the percentage amount (if applicable).
     *
     * @return float
     */
    public function getPercentage();

    /**
     * Return label for the payment method.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns the payment note.
     *
     * @return string
     */
    public function getNote();

    /**
     * Return the calculated total price for payment.
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Return percentage label if price is percentage.
     *
     * @return string
     */
    public function getPercentageLabel();

    /**
     * Process payment on checkout confirmation page.
     *
     * @param IsotopeProductCollection $objOrder  The order being places
     * @param \Module                  $objModule The checkout module instance
     *
     * @return mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Contao\Module $objModule);

    /**
     * Return a html form for checkout or false.
     *
     * @param IsotopeProductCollection $objOrder  The order being places
     * @param \Module                  $objModule The checkout module instance
     *
     * @return mixed
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Contao\Module $objModule);

    /**
     * Return the checkout review information.
     * Use this to return custom checkout information about this payment module.
     * Example: parial information about the used credit card.
     *
     * @return string
     */
    public function checkoutReview();

    /**
     * Get the checkout surcharge for this shipping method.
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return Payment|null
     */
    public function getSurcharge(IsotopeProductCollection $objCollection);
}
