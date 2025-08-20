<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

/**
 * Interface to allow shipping configuration of a collection item (usually a product).
 * If a product does not implement this interface, the item is always assumed to be shippable.
 */
interface ShippableInterface
{
    /**
     * Returns whether this product is exempt from shipping (e.g. a digital download).
     */
    public function isExemptFromShipping(): bool;

    /**
     * Returns whether this product can only be picked up but not shipped.
     */
    public function isPickupOnly(): bool;
}
