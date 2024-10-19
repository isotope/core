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

use Isotope\Model\Address;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\Shipping;

/**
 * IsotopeOrderableCollection describes a product collection that can have order information.
 */
interface IsotopeOrderableCollection extends IsotopeProductCollection
{
    /**
     * Return boolean whether collection has payment
     *
     * @return bool
     */
    public function hasPayment();

    /**
     * Return boolean whether collection requires payment
     *
     * @return bool
     */
    public function requiresPayment();

    /**
     * Return payment method for this collection
     *
     * @return IsotopePayment|Payment|null
     */
    public function getPaymentMethod();

    /**
     * Set payment method for this collection
     */
    public function setPaymentMethod(IsotopePayment $objPayment = null);

    /**
     * Return surcharge for current payment method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getPaymentSurcharge();

    /**
     * Get billing address for collection
     *
     * @return  \Isotope\Model\Address|null
     */
    public function getBillingAddress();

    /**
     * Set billing address for collection
     */
    public function setBillingAddress(Address $objAddress = null);

    /**
     * Return boolean whether collection has shipping
     *
     * @return bool
     */
    public function hasShipping();

    /**
     * Return boolean whether collection requires shipping
     *
     * @return bool
     */
    public function requiresShipping();

    /**
     * Return shipping method for this collection
     *
     * @return IsotopeShipping|Shipping|null
     */
    public function getShippingMethod();

    /**
     * Set shipping method for this collection
     */
    public function setShippingMethod(IsotopeShipping $objShipping = null);

    /**
     * Return surcharge for current shipping method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getShippingSurcharge();

    /**
     * Get shipping address for collection
     *
     * @return  Address|null
     */
    public function getShippingAddress();

    /**
     * Set shipping address for collection
     */
    public function setShippingAddress(Address $objAddress = null);

    /**
     * Returns the generated document number or empty string if not available.
     *
     * @return string
     */
    public function getDocumentNumber();
}
