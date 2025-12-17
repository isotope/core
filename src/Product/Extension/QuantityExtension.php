<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;

final class QuantityExtension implements ProductExtensionInterface
{
    private int|null $minimum = null;

    /**
     * @internal
     */
    public function __construct(
        private readonly CoreProduct $product,
    ) {
    }

    public function getMinimum(): int
    {
        if (null === $this->minimum) {
            $this->minimum = $this->product->getPrices()?->sort('getQuantity')->first()->getQuantity() ?? 1;
        }

        return $this->minimum;
    }

    // TODO: add maximum quantity
    // TODO: add purchase unit (e.g. price is per packs of 6)
    // TODO: add purchase steps (e.g. can only be sold in multiple of 6)
}
