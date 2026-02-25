<?php

namespace Isotope\CoreBundle\Store\Weight;

use Isotope\CoreBundle\Util\FloatComparator;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Weight>
 */
class Scale extends AbstractCollection
{
    public function getType(): string
    {
        return Weight::class;
    }

    /**
     * Standardize and calculate the total of multiple weights.
     * Based on formulas found at http://jumk.de/calc/gewicht.shtml
     */
    public function amountIn(WeightUnit $unit): Weight
    {
        if ($this->isEmpty()) {
            return new Weight(0, $unit);
        }

        $amount = 0;

        foreach ($this as $weight) {
            $amount += $weight->getAmountInUnit($unit);
        }

        return new Weight($amount, $unit);
    }

    /**
     * Check if weight on scale is less than given weight.
     */
    public function isLessThan(Weight $weight): bool
    {
        return FloatComparator::lessThan(
            $this->amountIn($weight->getUnit())->getAmount(),
            $weight->getAmount(),
        );
    }

    /**
     * Check if weight on scale is equal to or less than given weight.
     */
    public function isEqualOrLessThan(Weight $weight): bool
    {
        return FloatComparator::lessThanOrEquals(
            $this->amountIn($weight->getUnit())->getAmount(),
            $weight->getAmount()
        );
    }

    /**
     * Check if weight on scale is more than given weight.
     */
    public function isMoreThan(Weight $weight): bool
    {
        return FloatComparator::greaterThan(
            $this->amountIn($weight->getUnit())->getAmount(),
            $weight->getAmount()
        );
    }

    /**
     * Check if weight on scale is equal to or more than given weight.
     */
    public function isEqualOrMoreThan(Weight $weight): bool
    {
        return FloatComparator::greaterThanOrEquals(
            $this->amountIn($weight->getUnit())->getAmount(),
            $weight->getAmount()
        );
    }
}
