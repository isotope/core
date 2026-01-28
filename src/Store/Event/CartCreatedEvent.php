<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class CartCreatedEvent extends Event
{
    public function __construct(
        private readonly Cart $cart,
        private readonly StoreContext $context,
    ) {
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }
}
