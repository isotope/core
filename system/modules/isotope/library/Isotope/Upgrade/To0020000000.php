<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

use Contao\BackendTemplate;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\System;

class To0020000000 extends System
{

    public function run($blnInstalled)
    {
        $this->checkForOld();
    }


    protected function checkForOld()
    {
        foreach (array(
            'tl_store', // tl_iso_config in 0.2
            'tl_iso_products',
            'tl_iso_producttypes',
            'tl_iso_prices',
            'tl_iso_price_tiers',
            'tl_iso_product_categories',
            'tl_iso_related_categories',
            'tl_iso_related_products',
            'tl_iso_labels',
            'tl_iso_groups',
            'tl_iso_rules',
            'tl_iso_rule_restrictions',
            'tl_iso_downloads',
            'tl_iso_attributes',
            'tl_iso_addresses',
            'tl_iso_payment_modules',
            'tl_iso_shipping_modules',
            'tl_iso_orders',
            'tl_iso_order_items',
            'tl_iso_cart',
            'tl_iso_cart_items',
            'tl_iso_mail',
            'tl_iso_mail_content'
        ) as $strOldTable) {
            if (Database::getInstance()->tableExists($strOldTable)) {
                $this->warnForOld();
            }
        }

        if (\array_key_exists('isotope_multilingual', System::getContainer()->getParameter('kernel.bundles'))) {
            $this->warnForOld();
        }
    }


    /**
     * Output warning about old Isotope version that can't be updated
     */
    protected function warnForOld()
    {
        $objTemplate = new BackendTemplate('be_iso_old');

        throw new ResponseException($objTemplate->getResponse());
    }
}
