<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Util\FloatComparator;

/**
 * Base price is a price to compare different products against each other. Assume you sell flour, by 500g or 1kg
 * package. The base price could then be "xx € per 100g".
 *
 * $productAmount is how much _value_ the product has, e.g. "500" or "1000"
 * $baseAmount is how much the reference value is, e.g. "100"
 * $label contains the textual representation, e.g. "%s per 100g"
 *
 * To calculate the base price, we need to take the product price, divided by the product amount, multiplied by the base.
 * 1000g flour at 10€ = 10 / 1000 * 100 = €1.00 per 100g
 * 500 flour at 6€ = 6 / 500 * 100 = €1.20 per 100g
 */
class BasePrice
{
    public function __construct(
        private float $productAmount,
        private float $baseAmount,
        private readonly string $label,
    ) {
        $this->productAmount = FloatComparator::cast($this->productAmount);
        $this->baseAmount = FloatComparator::cast($this->baseAmount);
    }

    public function getProductAmount(): float
    {
        return $this->productAmount;
    }

    public function getBaseAmount(): float
    {
        return $this->baseAmount;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
