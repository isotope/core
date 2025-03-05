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
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\Backend\Permission;
use Isotope\Model\Payment;
use Isotope\Model\Shipping;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


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
     * Get allowed CC types and return them as array
     *
     * @return array
     * @deprecated Deprecated since 2.2, to be removed in 3.0. Create your own DCA field instead.
     */
    public function getAllowedCCTypes(DataContainer $dc)
    {
        $arrCCTypes = array();

        /** @var Payment $objPayment */
        if (($objPayment = Payment::findByPk($dc->id)) !== null) {

            try {
                foreach ($objPayment->getAllowedCCTypes() as $type) {
                    $arrCCTypes[$type] = $GLOBALS['TL_LANG']['CCT'][$type];
                }

                return $arrCCTypes;

            } catch (\Exception $e) {
            }
        }

        return $arrCCTypes;
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
