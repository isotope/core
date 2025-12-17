<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\CoreBundle\Product\CoreProduct;

class ProductContext
{
    public function __construct(
        private readonly CoreProduct $product,
        private readonly StoreContext $context,
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
}
