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
 * IsotopePrice is the interface for a product price
 */
interface IsotopePrice
{

    /**
     * Return price
     * @param   int
     * @return  float
     */
    public function getAmount($intQuantity=1);

    /**
     * Return original price
     * @param   int
     * @return  float
     */
    public function getOriginalAmount($intQuantity=1);

    /**
     * Return net price (without taxes)
     * @param   int
     * @return  float
     */
    public function getNetAmount($intQuantity=1);

    /**
     * Return gross price (with all taxes)
     * @param   int
     * @return  float
     */
    public function getGrossAmount($intQuantity=1);

}
