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

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;


class OrderConditionsOnTop extends OrderConditions implements IsotopeCheckoutStep
{

    /**
     * Returns true if order conditions should be above everything
     * @return  bool
     */
    public function isAvailable()
    {
        if ($this->objModule->iso_order_conditions_position != 'top') {
            return false;
        }

        return parent::isAvailable();
    }
}
