<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\TaxClass;


use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\Backend\Permission;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class Callback extends Backend
{

    /**
     * Check permissions to edit table tl_iso_tax_class
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (Input::get('mod') != 'tax_class') {
            return;
        }

        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_tax_classes) || \count($user->iso_tax_classes) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $user->iso_tax_classes;
        }

        $GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['sorting']['root'] = $root;
    }
}
