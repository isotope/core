<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRate;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Util\FloatComparator;

class CalculatedPrice implements \JsonSerializable
{
    private bool $isRange = false;

    private string|null $unitLabel = null;

    private BasePrice|null $basePrice = null;

    public function __construct(
        private float $unitPrice,
        private float $totalPrice,
        private readonly CalculatedTaxCollection $calculatedTaxes = new CalculatedTaxCollection(),
        private readonly TaxRateCollection $taxRates = new TaxRateCollection(),
        private readonly TaxRateCollection $includedTaxRates = new TaxRateCollection(),
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

    /**
     * @return CalculatedTaxCollection<CalculatedTax>
     */
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

    public function getIncludedTaxRates(): TaxRateCollection
    {
        return $this->includedTaxRates;
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

        return (new CalculatedPrice($this->totalPrice, $this->totalPrice, $calculatedTaxes, $this->taxRates, $this->includedTaxRates, 1))
            ->setRange($this->isRange)
            ->setUnitLabel($this->unitLabel)
            ->setBasePrice($this->basePrice)
        ;
    }

    public function jsonSerialize(): array
    {
        return [
            'unit_price' => $this->unitPrice,
            'quantity' => $this->quantity,
            'total_price' => $this->totalPrice,
            'unit_label' => $this->unitLabel,
            'is_range' => $this->isRange(),
            'calculated_taxes' => $this->calculatedTaxes->jsonSerialize(),
            'tax_rates' => [
                'rates' => $this->taxRates->jsonSerialize(),
                'included' => $this->includedTaxRates->jsonSerialize(),
            ],
        ];
    }

    public static function createFromJson(string $data): self|null
    {
        try {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        $price = new self(
            (float) ($data['unit_price'] ?? 0),
            (float) ($data['total_price'] ?? 0),
            new CalculatedTaxCollection(),
            new TaxRateCollection(),
        );

        foreach (($data['calculated_taxes'] ?? []) as $tax) {
            $price->getCalculatedTaxes()->put(
                $tax['id'],
                (new CalculatedTax($tax['id'], $tax['amount'], $tax['tax_rate'], $tax['price']))
                    ->setLabel($tax['label'])
                    ->setNumber((int) $tax['number'])
            );
        }

        foreach ((array) ($data['tax_rates']['rates'] ?? []) as $tax) {
            $price->getTaxRates()->add(new TaxRate($tax['id'], $tax['tax_rate'], $tax['percentage'], $tax['label']));
        }

        foreach ((array) ($data['tax_rates']['included'] ?? []) as $tax) {
            $price->getIncludedTaxRates()->add(new TaxRate($tax['id'], $tax['tax_rate'], $tax['percentage'], $tax['label']));
        }

        return $price;
    }
}
