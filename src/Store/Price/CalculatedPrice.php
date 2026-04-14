<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Util\FloatComparator;

class CalculatedPrice
{
    private bool $isRange = false;

    private string|null $unitLabel = null;

    private BasePrice|null $basePrice = null;

    public function __construct(
        private float $unitPrice,
        private float $totalPrice,
        private readonly CalculatedTaxCollection $calculatedTaxes = new CalculatedTaxCollection(),
        private readonly TaxRateCollection $taxRates = new TaxRateCollection(),
        private readonly int $quantity = 1,
    ) {
        $this->unitPrice = FloatComparator::cast($unitPrice);
        $this->totalPrice = FloatComparator::cast($totalPrice);
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

    public function getUnitLabel(): ?string
    {
        return $this->unitLabel;
    }

    public function setUnitLabel(?string $unitLabel): self
    {
        $this->unitLabel = $unitLabel;

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

    public function reduce(): self
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

        return (new CalculatedPrice($this->totalPrice, $this->totalPrice, $calculatedTaxes, $this->taxRates, 1))
            ->setRange($this->isRange)
            ->setUnitLabel($this->unitLabel)
            ->setBasePrice($this->basePrice)
        ;
    }
}
