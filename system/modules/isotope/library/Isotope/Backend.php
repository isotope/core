<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Backend as ContaoBackend;
use Contao\Controller;
use Contao\System;
use Isotope\CoreBundle\Model\Config;

/**
 * Provide methods to handle Isotope back end components.
 */
class Backend extends ContaoBackend
{
    /**
     * Get array of subdivisions, delay loading of file if not necessary
     *
     * @return array
     */
    public static function getSubdivisions()
    {
        static $arrSubdivisions = null;

        if (null === $arrSubdivisions) {

            System::loadLanguageFile('subdivisions');

            foreach ($GLOBALS['TL_LANG']['DIV'] as $strCountry => $arrSubdivision) {
                foreach ($arrSubdivision as $strCode => $varValue) {
                    if (\is_array($varValue)) {
                        $strGroup = $varValue[''];
                        unset($varValue['']);

                        $arrSubdivisions[$strCountry][$strCode][$strGroup] = $varValue;

                        continue;
                    }

                    $arrSubdivisions[$strCountry][$strCode] = $varValue;
                }
            }
        }

        return $arrSubdivisions;
    }

    /**
     * Returns the label for a subdivision of a country.
     *
     * @param string $country
     * @param string $subdivision
     *
     * @return string
     */
    public static function getLabelForSubdivision($country, $subdivision)
    {
        $country = strtolower($country);
        $arrSubdivisions = Backend::getSubdivisions();

        if (isset($arrSubdivisions[$country][$subdivision])) {
            return $arrSubdivisions[$country][$subdivision];
        }

        if (\is_array($arrSubdivisions[$country])) {
            foreach ($arrSubdivisions[$country] as $regionGroup) {
                if (\is_array($regionGroup)) {
                    foreach ($regionGroup as $regions) {
                        if (isset($regions[$subdivision])) {
                            return $regions[$subdivision];
                        }
                    }
                }
            }
        }

        return '';
    }
}
