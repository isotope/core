<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Address;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Isotope\Model\Address;

class Callback extends Backend
{

    /**
     * Generate and return the address label
     * @param array
     * @return string
     */
    public function renderLabel($arrAddress)
    {
        $objAddress = new Address();
        $objAddress->setRow($arrAddress);
        $strBuffer = $objAddress->generate();

        $strBuffer .= '<div style="color:#b3b3b3;margin-top:8px">' . $GLOBALS['TL_LANG']['tl_iso_address']['store_id'][0] . ' ' . $arrAddress['store_id'];

        return $strBuffer . '</div>';
    }
}
