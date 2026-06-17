<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

use Isotope\CoreBundle\Store\Weight\Weight;

final readonly class ShippingExtension implements ProductExtensionInterface
{
    /**
     * @internal
     */
    public function __construct(
        private bool $exemptFromShipping,
        private bool $pickupOnly,
        private Weight|null $weight,
        private float|null $price,
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

    public function getPrice(): float|null
    {
        return $this->price;
    }
}
