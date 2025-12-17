<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;

final class ShippingExtension implements ProductExtensionInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CoreProduct $product,
    ) {
    }

    public function isExemptFromShipping(): bool
    {
        if ($this->product->shipping_exempt) {
            return true;
        }

        return '1' === $this->product->getProductType()->shipping_exempt;
    }

    public function isPickupOnly(): bool
    {
        if ($this->product->shipping_pickup) {
            return true;
        }

        return '2' === $this->product->getProductType()->shipping_exempt;
    }
}
