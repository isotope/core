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

namespace Isotope\Units\Mass;

class Weight implements Weighable
{

    /**
     * Weight amount
     * @param   float
     */
    protected $fltValue;

    /**
     * Weight unit
     * @param   string
     */
    protected $strUnit;


    public function __construct($fltValue, $strUnit)
    {
        $this->fltValue = $fltValue;
        $this->strUnit = (string) $strUnit;
    }

    public function getWeightValue()
    {
        return $this->fltValue;
    }

    public function getWeightUnit()
    {
        return $this->strUnit;
    }

    /**
     * Create weight object from timePeriod widget value
     * @param   mixed
     * @return  Weight|null
     */
    public static function createFromTimePeriod($arrData)
    {
        $arrData = deserialize($arrData);

        if (empty($arrData)
            || !is_array($arrData)
            || $arrData['value'] === ''
            || $arrData['unit'] === ''
            || !in_array($arrData['unit'], Unit::getAll())) {
            return null;
        }

        return new static($arrData['value'], $arrData['unit']);
    }
}
