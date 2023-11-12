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
use Contao\System;
use Haste\Units\Dimension\Unit;
use Isotope\Interfaces\IsotopeWeighable;
use UnitConverter\UnitConverter;

class Weight implements IsotopeWeighable
{
private static UnitConverter $unitConverter;

    public function __construct(
        UnitConverter $unitConverter,
        private float $fltValue,
        private string $strUnit
    ) {
        self::$unitConverter = $unitConverter;
    }

    /**
     * @inheritdoc
     */
    public function getWeightValue()
    {
        return $this->fltValue;
    }

    /**
     * @inheritdoc
     */
    public function getWeightUnit()
    {
        return $this->strUnit;
    }

    /**
     * Create weight object from timePeriod widget value
     * @param   mixed $arrData
     * @return  Weight|null
     */
    public static function createFromTimePeriod($arrData)
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

        return new self($arrData['value'], $arrData['unit']);
    }
}
