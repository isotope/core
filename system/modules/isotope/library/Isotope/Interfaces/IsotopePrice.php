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
 * IsotopePrice is the interface for a product price
 */
interface IsotopePrice
{

    /**
     * Return true if more than one price is available
     *
     * @return bool
     */
    public function hasTiers();

    /**
     * Return price
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getAmount($intQuantity = 1);

    /**
     * Return original price
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getOriginalAmount($intQuantity = 1);

    /**
     * Return net price (without taxes)
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getNetAmount($intQuantity = 1);

    /**
     * Return gross price (with all taxes)
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getGrossAmount($intQuantity = 1);

    /**
     * Generate price for HTML rendering
     *
     * @return string
     */
    public function generate();
}
