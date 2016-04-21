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
 * OrderConditionsAfterProducts checkout step shows conditions form after the product list.
 */
class OrderConditionsAfterProducts extends OrderConditions implements IsotopeCheckoutStep
{
    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        if ('after' !== $this->objModule->iso_order_conditions_position) {
            return false;
        }

        return parent::isAvailable();
    }
}
