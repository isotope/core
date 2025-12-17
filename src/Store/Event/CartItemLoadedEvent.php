<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class CartItemLoadedEvent extends Event
{
    private bool $skip = false;

    public function __construct(
        private readonly LineItem $lineItem,
        private readonly Cart $cart,
        private readonly StoreContext $context,
    ) {
    }

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public function isSkipped(): bool
    {
        return $this->skip;
    }

    public function skip(): void
    {
        $this->skip = true;
        $this->stopPropagation();
    }
}
