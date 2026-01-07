<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Price;

class Price
{
    private int $variantId = 0;

    public function __construct(
        private float $amount,
        private int $quantity,
        private int|null $taxId,
    ) {
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTaxId(): int|null
    {
        return $this->taxId;
    }

    public function getVariantId(): int
    {
        return $this->variantId;
    }

    public function setVariantId(int $variantId): self
    {
        $this->variantId = $variantId;

        return $this;
    }
}
