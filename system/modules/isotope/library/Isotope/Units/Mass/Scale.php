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

class Scale
{

    /**
     * Scale weight objects
     * @var Weight[]
     */
    protected $arrWeights = array();


    /**
     * Add weight to the scale
     * @param   WeightInterface
     */
    public function add(Weighable $objWeight)
    {
        $this->arrWeights[] = $objWeight;

        return $this;
    }


    /**
     * Remove a weight object from scale
     * @param   WeightInterface
     * @return  self
     */
    public function remove(Weighable $objWeight)
    {
        $key = array_search($objWeight, $this->arrWeights, true);

        if ($key !== false) {
            unset($this->arrWeights[$key]);
        }

        return $this;
    }

    /**
     * Standardize and calculate the total of multiple weights
     *
     * It's probably faster in theory to convert only the total to the final unit, and not each product weight.
     * However, we might loose precision, not sure about that.
     * Based on formulas found at http://jumk.de/calc/gewicht.shtml
     * @param array
     * @param string
     * @return mixed
     */
    public function amountIn($strUnit)
    {
        if (empty($this->arrWeights)) {
            return 0;
        }

        $fltWeight = 0;

        foreach ($this->arrWeights as $objWeight) {
            if ($objWeight->getWeightValue() > 0) {
                $fltWeight += Unit::convert(floatval($objWeight->getWeightValue()), $objWeight->getWeightUnit(), Unit::KILOGRAM);
            }
        }

        return Unit::convert($fltWeight, Unit::KILOGRAM, $strUnit);
    }

    /**
     * Check if weight on scale is less than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isLessThan(Weighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) < $objWeight->getWeightValue();
    }

    /**
     * Check if weight on scale is equal to or less than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isEqualOrLessThan(Weighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) <= $objWeight->getWeightValue();
    }

    /**
     * Check if weight on scale is more than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isMoreThan(Weighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) > $objWeight->getWeightValue();
    }

    /**
     * Check if weight on scale is equal to or more than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isEqualOrMoreThan(Weighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) >= $objWeight->getWeightValue();
    }
}
