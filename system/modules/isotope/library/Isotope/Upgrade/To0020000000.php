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

namespace Isotope\Upgrade;


class To0020000000 extends \System
{

    public function run()
    {
        $this->import('Database');

        $this->convertToClassType();
    }

    /**
     * Convert gallery, shipping, payment types to new class names
     */
    public function convertToClassType()
    {
        // Shipping methods
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Flat' WHERE type='flat'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='OrderTotal' WHERE type='order_total'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='WeightTotal' WHERE type='weight_total'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='UPS' WHERE type='ups'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='USPS' WHERE type='usps'");

        // Payment methods
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Cash' WHERE type='cash'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='AuthorizeDotNet' WHERE type='authorizedotnet'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Cybersource' WHERE type='cybersource'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Datatrans' WHERE type='datatrans'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Expercash' WHERE type='expercash'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Payone' WHERE type='payone'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Paypal' WHERE type='paypal'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='PaypalPayflowPro' WHERE type='paypalpayflowpro'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Postfinance' WHERE type='postfinance'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Sparkasse' WHERE type='sparkasse'");

        // Galleries
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Standard' WHERE type='standard'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Inline' WHERE type='inline'");
        $this->Database->query("UPDATE tl_iso_payment_modules SET type='Zoom' WHERE type='zoom'");
    }
}
