<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;

class ProductContext
{
    public function __construct(
        private readonly CoreProduct $product,
        private readonly StoreContext $context,
        private readonly CalculatedPrice|null $price = null,
    ) {
    }

    public function getProduct(): CoreProduct
    {
        return $this->product;
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public function getPrice(): CalculatedPrice|null
    {
        return $this->price ?? $this->product->getPrice();
    }
}
