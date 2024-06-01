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

namespace Isotope\Units;

abstract class Converter
{

    /**
     * Convert from one to another unit
     * @param   float
     * @param   string
     * @param   string
     * @return  float
     */
    public static function convert($varValue, $strSourceUnit, $strTargetUnit)
    {
        if ($strSourceUnit == $strTargetUnit) {
            return $varValue;
        }

        if ($strSourceUnit != static::getBase()) {
            $varValue = ($varValue / static::getFactor($strSourceUnit));
        }

        return $varValue * static::getFactor($strTargetUnit);
    }

    /**
     * Return factor to calculate the unit to the base unit
     * @param   string
     * @return  float
     */
    public static function getFactor($strUnit)
    {
        return static::$arrFactors[$strUnit];
    }

    public static function getAll()
    {
        throw new \BadMethodCallException('getAll() must be implemented by a concrete child class.');
    }

    public static function getBase()
    {
        throw new \BadMethodCallException('getBase() must be implemented by a concrete child class.');
    }
}
