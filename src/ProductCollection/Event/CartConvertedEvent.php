<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class CartConvertedEvent extends Event
{
    /**
     * @var array<mixed>
     */
    private array $convertedCart;

    /**
     * @param array<mixed> $originalConvertedCart
     */
    public function __construct(
        private readonly Cart $cart,
        private readonly array $originalConvertedCart,
        private readonly StoreContext $storeContext,
    ) {
        $this->convertedCart = $originalConvertedCart;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOriginalConvertedCart(): array
    {
        return $this->originalConvertedCart;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConvertedCart(): array
    {
        return $this->convertedCart;
    }

    /**
     * @param array<string, mixed> $convertedCart
     */
    public function setConvertedCart(array $convertedCart): void
    {
        $this->convertedCart = $convertedCart;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->storeContext;
    }
}
