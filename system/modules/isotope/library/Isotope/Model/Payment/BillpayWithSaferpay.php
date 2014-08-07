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

namespace Isotope\Model\Payment;

use Isotope\Isotope;


class BillpayWithSaferpay extends Saferpay
{

    public function isAvailable()
    {
        $objAddress = Isotope::getCart()->getBillingAddress();

        if (null === $objAddress || !in_array($objAddress->country, array('de', 'ch', 'at'))) {
            return false;
        }

        return parent::isAvailable();
    }
}
