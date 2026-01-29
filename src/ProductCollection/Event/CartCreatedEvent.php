<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class CartCreatedEvent extends Event
{
    public function __construct(
        public readonly Cart $cart,
        public readonly StoreContext $context,
    ) {
    }
}
