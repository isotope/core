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
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\Backend\Permission;
use Isotope\Model\Shipping;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class Callback extends Backend
{

    /**
     * Check permissions to edit table tl_iso_shipping
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('shipping' !== Input::get('mod')) {
            return;
        }

        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_shipping_modules)
            || 0 === \count($user->iso_shipping_modules)
        ) {
            $root = array(0);
        } else {
            $root = $user->iso_shipping_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_shipping']['list']['sorting']['root'] = $root;
    }

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
