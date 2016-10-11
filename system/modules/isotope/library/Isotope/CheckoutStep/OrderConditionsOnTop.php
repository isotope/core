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

/**
 * OrderConditionsOnTop checkout step shows conditions form before info like address and payment/shipping methods.
 */
class OrderConditionsOnTop extends OrderConditions implements IsotopeCheckoutStep
{
    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        if ('top' !== $this->objModule->iso_order_conditions_position) {
            return false;
        }

        return parent::isAvailable();
    }
}
