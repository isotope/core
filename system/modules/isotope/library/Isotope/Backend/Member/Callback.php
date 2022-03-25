<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Member;

use Contao\Backend;
use Contao\System;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Cart;


class Callback extends Backend
{

    /**
     * Limit the member countries to the selection in store config
     * @param string
     */
    public function limitCountries($strTable)
    {
        if ('tl_member' !== $strTable || !Isotope::getConfig()->limitMemberCountries) {
            return;
        }

        $originalField = $GLOBALS['TL_DCA']['tl_member']['fields']['country'];

        unset($GLOBALS['TL_DCA']['tl_member']['fields']['country']['options']);

        $GLOBALS['TL_DCA']['tl_member']['fields']['country']['options_callback'] = function () use ($originalField) {
            if (isset($originalField['options_callback'])) {
                if (\is_array($originalField['options_callback'])) {
                    $callable = [
                        System::importStatic($originalField['options_callback'][0]),
                        $originalField['options_callback'][1],
                    ];
                } else {
                    $callable = $originalField['options_callback'];
                }

                $options = \call_user_func_array(
                    $callable,
                    \func_get_args()
                );
            } else {
                $options = (array) $originalField['options'];
            }

            $countries = array_unique(
                array_merge(
                    Isotope::getConfig()->getBillingCountries(),
                    Isotope::getConfig()->getShippingCountries()
                )
            );

            $countries = array_intersect_key(
                $options,
                array_flip($countries)
            );

            if (1 === \count($countries)) {
                $countryCodes = array_keys($countries);
                $GLOBALS['TL_DCA']['tl_member']['fields']['country']['default'] = $countryCodes[0];
            }

            return $countries;
        };
    }

    /**
     * Delete the cart when a member is deleted
     *
     * @param object $dc
     */
    public function deleteMemberCart($dc)
    {
        $carts = Cart::findBy('member', $dc->id);

        if (null !== $carts) {
            foreach ($carts as $cart) {
                $cart->delete();
            }
        }
    }
}
