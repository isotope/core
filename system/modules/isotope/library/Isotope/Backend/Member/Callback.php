<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope\Backend\Member;

use Isotope\Isotope;


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
}
