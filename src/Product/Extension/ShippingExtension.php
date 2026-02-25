<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

use Isotope\CoreBundle\Store\Weight\Weight;

final class ShippingExtension implements ProductExtensionInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly bool $exemptFromShipping,
        private readonly bool $pickupOnly,
        private readonly Weight|null $weight,
        private readonly float|null $price,
    ) {
    }

    public function isExemptFromShipping(): bool
    {
        return $this->exemptFromShipping;
    }

    public function isPickupOnly(): bool
    {
        return $this->pickupOnly;
    }

    public function getWeight(): Weight|null
    {
        return $this->weight;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }
}
