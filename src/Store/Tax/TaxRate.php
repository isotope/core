<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Isotope\CoreBundle\Util\FloatComparator;

class TaxRate
{
    public function __construct(
        private readonly int $id,
        private float $taxRate,
        private float $percentage = 100.0
    ) {
        $this->taxRate = FloatComparator::cast($taxRate);
        $this->percentage = FloatComparator::cast($percentage);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTaxRate(): float
    {
        return FloatComparator::cast($this->taxRate);
    }

    public function getPercentage(): float
    {
        return FloatComparator::cast($this->percentage);
    }
}
