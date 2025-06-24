<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Payment;

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Input;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\Shipping;


class Callback extends Backend
{

    /**
     * Check permissions to edit table tl_iso_payment
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('payment' !== Input::get('mod')) {
            return;
        }

        $user = BackendUser::getInstance();

        // Return if user is admin
        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_payment_modules) || \count($user->iso_payment_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $user->iso_payment_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_payment']['list']['sorting']['root'] = $root;
    }


    /**
     * Load shipping modules into the DCA (options_callback would not work due to numeric array keys)
     *
     * @param object $dc
     */
    public function loadShippingModules($dc)
    {
        $arrModules   = array(-1 => $GLOBALS['TL_LANG'][$dc->table]['no_shipping']);
        $objShippings = Shipping::findAll(array('order' => 'name'));

        if (null !== $objShippings) {
            foreach ($objShippings as $objShipping) {
                $arrModules[$objShipping->id] = $objShipping->name;
            }
        }

        $GLOBALS['TL_DCA'][$dc->table]['fields']['shipping_modules']['options']   = array_keys($arrModules);
        $GLOBALS['TL_DCA'][$dc->table]['fields']['shipping_modules']['reference'] = $arrModules;
    }
}
