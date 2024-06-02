<?php

/**
 * Haste utilities for Contao Open Source CMS
 *
 * Copyright (C) 2012-2013 Codefog & terminal42 gmbh
 *
 * @package    Haste
 * @link       http://github.com/codefog/contao-haste/
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Haste\Units\Mass;

use Isotope\Haste\Units\Converter;

class Unit extends Converter
{

    /**
     * Available weight units
     */
    const MILIGRAM = 'mg';
    const GRAM = 'g';
    const KILOGRAM = 'kg';
    const METRICTON = 't';
    const CARAT = 'cz';
    const OUNCE = 'oz';
    const POUND = 'lb';
    const STONE = 'st';
    const GRAIN = 'grain';

    /**
     * Conversion factor from base unit
     * @var array
     */
    protected static $arrFactors = array(
        'mg'    => 1000000,
        'g'     => 1000,
        'kg'    => 1,
        't'     => 0.001,
        'cz'    => 5000,
        'oz'    => 35.2733686067,
        'lb'    => 2.2046226218487757,
        'st'    => 0.1574730444,
        'grain' => 15432.3583529414
    );

    /**
     * Get base unit for this mass
     * @return  string
     */
    public static function getBase()
    {
        return static::KILOGRAM;
    }

    /**
     * Get supported weight units
     * @return  array
     */
    public static function getAll()
    {
        return array(
            static::MILIGRAM,
            static::GRAM,
            static::KILOGRAM,
            static::METRICTON,
            static::CARAT,
            static::OUNCE,
            static::POUND,
            static::STONE,
            static::GRAIN,
        );
    }
}
