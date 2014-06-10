<?php

namespace Isotope\Interfaces;


/**
 * IsotopeShippingOptions interface defines an Isotope shipping method with options form
 */
interface IsotopeShippingOptions
{

    /**
     * Return from with shipping options
     */
    public function getShippingOptions($objModule);
}
