<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Shipping;


use Contao\Backend;
use Isotope\CoreBundle\Model\Shipping;

class Callback extends Backend
{
    public function hideLabelAndNotes($dc)
    {
        if ($dc->id) {
            $shipping = Shipping::findByPk($dc->id);

            if ($shipping->type === 'group' && $shipping->inherit) {
                unset(
                    $GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['label'],
                    $GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['note']
                );
            }
        }
    }
}
