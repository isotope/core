<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\Haste\Util\Format;


class Callback extends Backend
{

    /**
     * Check permissions to edit table tl_iso_producttype
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('producttypes' !== Input::get('mod')) {
            return;
        }

        $objBackendUser = BackendUser::getInstance();

        if ($objBackendUser->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($objBackendUser->iso_product_types) || \count($objBackendUser->iso_product_types) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $objBackendUser->iso_product_types;
        }

        $GLOBALS['TL_DCA']['tl_iso_producttype']['list']['sorting']['root'] = $root;
    }

}
