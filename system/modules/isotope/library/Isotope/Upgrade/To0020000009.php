<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


class To0020000009 extends \System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {

            if (\Database::getInstance()->fieldExists('address1_id', 'tl_iso_product_collection')
                && !\Database::getInstance()->fieldExists('billing_address_id', 'tl_iso_product_collection')
            ) {
                \Database::getInstance()->query("
                    ALTER TABLE tl_iso_product_collection
                    CHANGE COLUMN `address1_id` `billing_address_id` int(10) unsigned NOT NULL default '0'
                ");
            }

            if (\Database::getInstance()->fieldExists('address2_id', 'tl_iso_product_collection')
                && !\Database::getInstance()->fieldExists('shipping_address_id', 'tl_iso_product_collection')
            ) {
                \Database::getInstance()->query("
                    ALTER TABLE tl_iso_product_collection
                    CHANGE COLUMN `address2_id` `shipping_address_id` int(10) unsigned NOT NULL default '0'
                ");
            }
        }
    }
}
