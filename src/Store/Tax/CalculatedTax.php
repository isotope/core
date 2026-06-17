<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Isotope\CoreBundle\Util\FloatComparator;

class CalculatedTax implements \JsonSerializable
{
    private string|null $label = null;

    private int $number = 0;

    public function __construct(
        private readonly string $id,
        private float $amount,
        private float $taxRate,
        private float $price,
    ) {
        $this->amount = FloatComparator::cast($amount);
        $this->taxRate = FloatComparator::cast($taxRate);
        $this->price = FloatComparator::cast($price);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = FloatComparator::cast($amount);
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = FloatComparator::cast($price);
    }

    public function getLabel(): string|null
    {
        return $this->label;
    }

    public function setLabel(string|null $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function multiply(int $quantity): void
    {
        $this->amount = FloatComparator::cast($this->amount * $quantity);
        $this->price = FloatComparator::cast($this->price * $quantity);
    }

    public function increment(self $calculatedTax): void
    {
        $this->amount = FloatComparator::cast($this->amount + $calculatedTax->getAmount());
        $this->price = FloatComparator::cast($this->price + $calculatedTax->getPrice());
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'tax_rate' => $this->taxRate,
            'price' => $this->price,
            'label' => $this->label,
            'number' => $this->number,
        ];
    }
}
