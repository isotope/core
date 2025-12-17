<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Event;

use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class ProductCreatedEvent extends Event
{
    public function __construct(
        public readonly Product $product,
        public readonly ProductDto $dto,
        public readonly StoreContext $context,
    ) {
    }
}
