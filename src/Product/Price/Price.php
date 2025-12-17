<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Price;

use Isotope\CoreBundle\Store\Context\StoreContext;

class Price
{
    private int $variantId = 0;

    public function __construct(
        private float $price,
        private int $quantity,
        private int|null $taxId,
        private int|null $configId,
        private int|null $groupId,
        private \DateTimeImmutable|null $start,
        private \DateTimeImmutable|null $stop,
    ) {
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTaxId(): ?int
    {
        return $this->taxId;
    }

    public function getConfigId(): ?int
    {
        return $this->configId;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function getStop(): ?\DateTimeImmutable
    {
        return $this->stop;
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

    public function isApplicable(StoreContext $context): bool
    {
        if ($this->configId && $this->configId !== $context->getConfigId()) {
            return false;
        }

        if ($this->groupId && !\in_array($this->groupId, $context->getMemberGroups(), true)) {
            return false;
        }

        if ($this->start && $this->start > $context->getTimestamp()) {
            return false;
        }

        if ($this->stop && $this->stop < $context->getTimestamp()) {
            return false;
        }

        return true;
    }
}
