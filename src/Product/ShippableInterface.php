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
     *
     * If other products in the same cart are shippable, the checkout will ignore this product
     * and still ask for a shipping address and method for the other products.
     */
    public function isExemptFromShipping(): bool;

    /**
     * Returns whether this product can only be picked up but not shipped.
     *
     * If the product is pickup only, the checkout will never ask for a shipping address
     * or shipping method, because the user has to pick up items anyway.
     */
    public function isPickupOnly(): bool;
}
