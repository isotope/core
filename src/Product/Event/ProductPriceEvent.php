<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Event;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Symfony\Contracts\EventDispatcher\Event;

class ProductPriceEvent extends Event
{
    public function __construct(
        private readonly CoreProduct $product,
        private readonly StoreContext $context,
        private readonly array $configuration = [],
        private CalculatedPrice|null $price = null,
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

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getPrice(): CalculatedPrice|null
    {
        return $this->price;
    }

    public function setPrice(CalculatedPrice|null $price): self
    {
        $this->price = $price;

        return $this;
    }
}
