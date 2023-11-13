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

use Contao\System;
use Isotope\Interfaces\IsotopeWeighable;
use UnitConverter\UnitConverter;

class Scale
{
    /**
     * Scale weight objects
     * @var Weight[]
     */
    protected $arrWeights = array();

    private static UnitConverter $unitConverter;

    /**
     * Add weight to the scale
     */
    public function add(IsotopeWeighable $objWeight)
    {
        $this->arrWeights[] = $objWeight;

        return $this;
    }

    /**
     * Remove a weight object from scale
     * @param   WeightInterface
     * @return  self
     */
    public function remove(IsotopeWeighable $objWeight)
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
     */
    public function amountIn(string $strUnit): float
    {
        if (empty($this->arrWeights)) {
            return 0;
        }

        $unitConverter = System::getContainer()->get('isotope.unit_converter');

        $fltWeight = 0.0;

        foreach ($this->arrWeights as $objWeight) {
            if ($objWeight->getWeightValue() > 0) {
                $fltWeight += $unitConverter->convert((string)$objWeight->getWeightValue())->from($objWeight->getWeightUnit())->to("kg");
            }
        }

        return $unitConverter->convert((string)$fltWeight)->from("kg")->to($strUnit);
    }

    /**
     * Check if weight on scale is less than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isLessThan(IsotopeWeighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) < $objWeight->getWeightValue();
    }

    /**
     * Check if weight on scale is equal to or less than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isEqualOrLessThan(IsotopeWeighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) <= $objWeight->getWeightValue();
    }

    /**
     * Check if weight on scale is more than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isMoreThan(IsotopeWeighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) > $objWeight->getWeightValue();
    }

    /**
     * Check if weight on scale is equal to or more than given weight
     * @param   WeightInterface
     * @return  bool
     */
    public function isEqualOrMoreThan(IsotopeWeighable $objWeight)
    {
        return $this->amountIn($objWeight->getWeightUnit()) >= $objWeight->getWeightValue();
    }
}
