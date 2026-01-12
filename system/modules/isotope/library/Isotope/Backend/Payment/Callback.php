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
