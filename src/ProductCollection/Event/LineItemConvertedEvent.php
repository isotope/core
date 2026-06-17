<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class LineItemConvertedEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    private array $convertedLineItem;

    /**
     * @param array<string, mixed> $originalConvertedItem
     */
    public function __construct(
        private readonly LineItem $lineItem,
        private readonly array $originalConvertedItem,
        private readonly Cart $cart,
        private readonly StoreContext $storeContext,
    ) {
        $this->convertedLineItem = $originalConvertedItem;
    }

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOriginalConvertedItem(): array
    {
        return $this->originalConvertedItem;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConvertedLineItem(): array
    {
        return $this->convertedLineItem;
    }

    /**
     * @param array<string, mixed> $convertedLineItem
     */
    public function setConvertedLineItem(array $convertedLineItem): void
    {
        $this->convertedLineItem = $convertedLineItem;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->storeContext;
    }
}
