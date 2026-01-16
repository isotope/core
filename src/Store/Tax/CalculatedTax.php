<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Isotope\CoreBundle\Util\FloatComparator;

class CalculatedTax
{
    protected float $tax = 0;

    protected float $taxRate;

    protected float $price = 0;

    protected string|null $label = null;

    protected int $number = 0;

    public function __construct(
        private readonly string $id,
        float $tax,
        float $taxRate,
        float $price,
    ) {
        $this->tax = FloatComparator::cast($tax);
        $this->taxRate = FloatComparator::cast($taxRate);
        $this->price = FloatComparator::cast($price);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setTax(float $tax): void
    {
        $this->tax = FloatComparator::cast($tax);
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

    public function setLabel(?string $label): CalculatedTax
    {
        $this->label = $label;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): CalculatedTax
    {
        $this->number = $number;
        return $this;
    }

    public function multiply(int $quantity): void
    {
        $this->tax = FloatComparator::cast($this->tax * $quantity);
        $this->price = FloatComparator::cast($this->price * $quantity);
    }

    public function increment(self $calculatedTax): void
    {
        $this->tax = FloatComparator::cast($this->tax + $calculatedTax->getTax());
        $this->price = FloatComparator::cast($this->price + $calculatedTax->getPrice());
    }
}
