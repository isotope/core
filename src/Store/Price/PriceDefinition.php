<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Util\FloatComparator;

class PriceDefinition
{
    public const int SPLIT_TAX = -1;

    private float $amount;

    private string|null $unitLabel = null;

    private string|null $reference = null;

    public function __construct(
        float $amount,
        private int|null $taxClassId,
        private int $quantity = 1,
        private bool $percentage = false,
    ) {
        $this->amount = FloatComparator::cast($amount);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTaxClassId(): int|null
    {
        return $this->taxClassId;
    }

    public function isPercentage(): bool
    {
        return $this->percentage;
    }

    public function getReference(): string|null
    {
        return $this->reference;
    }

    public function getUnitLabel(): string|null
    {
        return $this->unitLabel;
    }

    public function setUnitLabel(string|null $unitLabel): self
    {
        $this->unitLabel = $unitLabel;

        return $this;
    }

    public function setReference(string|null $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function withAmount(float $amount): self
    {
        $definition = clone $this;
        $definition->amount = $amount;

        return $definition;
    }

    public function withQuantity(int $quantity): self
    {
        $definition = clone $this;
        $definition->quantity = $quantity;

        return $definition;
    }

    public static function fromString(string $price, int $taxClass): self|null
    {
        if ('' === $price) {
            return null;
        }

        $isPercentage = str_ends_with($price, '%');
        $amount = $isPercentage ? substr($price, 0, -1) : $price;

        $definition = new self((float) $amount, $taxClass, percentage: $isPercentage);

        if ($isPercentage) {
            $definition->setUnitLabel($price);
        }

        return $definition;
    }
}
