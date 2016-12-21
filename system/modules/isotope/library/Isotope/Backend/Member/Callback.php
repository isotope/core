<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Member;

use Isotope\Isotope;
use Isotope\Model\ProductCollection\Cart;


class Callback extends \Backend
{

    /**
     * Limit the member countries to the selection in store config
     * @param string
     */
    public function limitCountries($strTable)
    {
        if ($strTable != 'tl_member' || !Isotope::getConfig()->limitMemberCountries) {
            return;
        }

        $arrCountries = array_unique(
            array_merge(
                Isotope::getConfig()->getBillingCountries(),
                Isotope::getConfig()->getShippingCountries()
            )
        );

        $arrCountries = array_intersect_key(
            $GLOBALS['TL_DCA']['tl_member']['fields']['country']['options'],
            array_flip($arrCountries)
        );

        $GLOBALS['TL_DCA']['tl_member']['fields']['country']['options'] = $arrCountries;

        if (count($arrCountries) == 1) {
            $arrCountryCodes = array_keys($arrCountries);
            $GLOBALS['TL_DCA']['tl_member']['fields']['country']['default'] = $arrCountryCodes[0];
        }
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
