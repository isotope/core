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

use Isotope\Model\ProductCollectionSurcharge\Shipping;


/**
 * IsotopeShipping interface defines an Isotope shipping method
 */
interface IsotopeShipping
{
    /**
     * Returns the ID of this shipping method.
     *
     * @return int
     */
    public function getId();

    /**
     * Return boolean flag if the shipping method is available.
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Return true if shipping price is not a fixed amount.
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
     * Return label for the shipping method.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns the shipping note.
     *
     * @return string
     */
    public function getNote();

    /**
     * Return percentage label if price is percentage.
     *
     * @return string
     */
    public function getPercentageLabel();

    /**
     * Return the calculated total price for shipping.
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Return the checkout review information.
     * Use this to return custom checkout information about this shipping module.
     * Example: Information about tracking codes.
     *
     * @return string
     */
    public function checkoutReview();

    /**
     * Get the checkout surcharge for this shipping method.
     *
     *
     * @return Shipping|null
     */
    public function getSurcharge(IsotopeProductCollection $objCollection);
}
