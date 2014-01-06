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


class OrderConditionsBeforeProducts extends OrderConditions implements IsotopeCheckoutStep
{

    /**
     * Returns true if order conditions should be before products
     * @return  bool
     */
    public function isAvailable()
    {
        if ($this->objModule->iso_order_conditions_position != 'before') {
            return false;
        }

        return parent::isAvailable();
    }
}
