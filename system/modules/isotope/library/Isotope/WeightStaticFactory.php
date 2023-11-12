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

use Contao\StringUtil;
use Isotope\Weight;
use UnitConverter\UnitConverter;

class WeightStaticFactory
{
    private static UnitConverter $unitConverter;

    /**
     * Create weight object from timePeriod widget value
     * @param   mixed $arrData
     * @return  Weight|null
     */
    public static function createWeightFromTimePeriod($arrData): Weight
    {
        $arrData = StringUtil::deserialize($arrData);

        if (
            empty($arrData)
            || !is_array($arrData)
            || $arrData['value'] === ''
            || $arrData['unit'] === ''
            || !in_array($arrData['unit'], self::$unitConverter->getRegistry()->listUnits('mass'))
        ) {
            return null;
        }

        return new Weight($arrData['value'], $arrData['unit']);
    }
}
