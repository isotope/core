<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Isotope\CoreBundle\Util\FloatComparator;

class TaxRate implements \JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private float $taxRate,
        private float $percentage = 100.0,
        private readonly string|null $label = null,
    ) {
        $this->taxRate = FloatComparator::cast($taxRate);
        $this->percentage = FloatComparator::cast($percentage);
    }

    public function getId(): string
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

    public function getLabel(): string|null
    {
        return $this->label;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'tax_rate' => $this->taxRate,
            'percentage' => $this->percentage,
            'label' => $this->label,
        ];
    }
}
