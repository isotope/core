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

    public function run($blnInstalled)
    {
        $this->checkForOld();
    }


    protected function checkForOld()
    {
        foreach (array(
            'tl_store', // tl_iso_config in 0.2
            'tl_iso_orders',
            'tl_iso_order_items',
            'tl_iso_cart',
            'tl_iso_cart_items',
            'tl_iso_mail',
            'tl_iso_mail_content'
        ) as $strOldTable) {
            if (\Database::getInstance()->tableExists($strOldTable)) {
                $this->warnForOld();
            }
        }

        if (in_array('isotope_multilingual', \Config::getInstance()->getActiveModules())) {
            $this->warnForOld();
        }
    }


    /**
     * Output warning about old Isotope version that can't be updated
     */
    protected function warnForOld()
    {
        $objTemplate = new \BackendTemplate('be_iso_old');

        $objTemplate->output();
        exit;
    }
}
