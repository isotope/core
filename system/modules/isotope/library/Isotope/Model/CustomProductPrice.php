<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

/**
 * CustomProductPrice defines a custom price of a product
 */
class CustomProductPrice extends ProductPrice
{
    /**
     * Price
     * @var float
     */
    protected $price;

    /**
     * Set the price
     *
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = (float)$price;
    }

    /**
     * Return value for a price tier, finding closest match
     *
     * @param int $intTier
     *
     * @return float
     */
    public function getValueForTier($intTier)
    {
        return $this->price;
    }
}
