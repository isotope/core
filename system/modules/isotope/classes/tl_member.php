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

namespace Isotope;


/**
 * Class tl_module
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_member extends \Backend
{

    /**
     * Limit the member countries to the selection in store config
     * @param string
     */
    public function limitCountries($strTable)
    {
        if ($strTable != 'tl_member' || !Isotope::getInstance()->Config->limitMemberCountries) {
            return;
        }

        $arrCountries = array_unique(
            array_merge(
                (array) deserialize($this->Config->billing_countries),
                (array) deserialize($this->Config->shipping_countries)
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
