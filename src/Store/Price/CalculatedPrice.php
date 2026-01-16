<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;

class CalculatedPrice
{
    private BasePrice|null $basePrice = null;

    public function __construct(
        private readonly float $unitPrice,
        private readonly float $totalPrice,
        private readonly CalculatedTaxCollection $calculatedTaxes,
        private readonly TaxRateCollection $taxRates,
        private readonly int $quantity = 1,
        private bool $isRange = false,
    ) {
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getCalculatedTaxes(): CalculatedTaxCollection
    {
        return $this->calculatedTaxes;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
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

    public function getBasePrice(): BasePrice|null
    {
        return $this->basePrice;
    }

    public function setBasePrice(BasePrice|null $basePrice): self
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    public function reduce(): CalculatedPrice
    {
        if (1 === $this->quantity) {
            return $this;
        }

        $calculatedTaxes = new CalculatedTaxCollection();

        /** @var CalculatedTax $tax */
        foreach ($this->getCalculatedTaxes() as $tax) {
            $new = clone $tax;
            $new->multiply($this->quantity);
            $calculatedTaxes->put($new->getId(), $new);
        }

        return new CalculatedPrice($this->totalPrice, $this->totalPrice, $calculatedTaxes, $this->taxRates, 1, $this->isRange);
    }
}
