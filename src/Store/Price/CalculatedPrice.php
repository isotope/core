<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;

class CalculatedPrice
{
    private BasePrice|null $basePrice = null;

    public function __construct(
        public float $unitPrice,
        public float $totalPrice,
        public CalculatedTaxCollection $calculatedTaxes,
        private TaxRateCollection $taxRates,
        public int $quantity = 1,
        private bool $isRange = false,
    ) {
    }

    public function getTaxRates(): TaxRateCollection
    {
        return $this->taxRates;
    }

    public function isRange(): bool
    {
        return $this->isRange;
    }

    public function setRange(bool $isRange): self
    {
        $this->isRange = $isRange;
        return $this;
    }

    public function getBasePrice(): ?BasePrice
    {
        return $this->basePrice;
    }

    public function setBasePrice(?BasePrice $basePrice): CalculatedPrice
    {
        $this->basePrice = $basePrice;
        return $this;
    }
}
