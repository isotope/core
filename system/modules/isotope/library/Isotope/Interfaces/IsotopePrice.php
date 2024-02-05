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
     * @param int   $intQuantity
     *
     * @return float
     */
    public function getAmount($intQuantity = 1, array $arrOptions = array());

    /**
     * Return original price
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getOriginalAmount($intQuantity = 1, array $arrOptions = array());

    /**
     * Return net price (without taxes)
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getNetAmount($intQuantity = 1, array $arrOptions = array());

    /**
     * Return gross price (with all taxes)
     *
     * @param int $intQuantity
     *
     * @return float
     */
    public function getGrossAmount($intQuantity = 1, array $arrOptions = array());

    /**
     * Generate price for HTML rendering
     *
     * @param bool  $blnShowTiers
     * @param int   $intQuantity
     *
     * @return string
     */
    public function generate($blnShowTiers = false, $intQuantity = 1, array $arrOptions = array());
}
