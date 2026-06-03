<?php declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class OrderConvertedEvent extends Event
{
    public function __construct(
        private readonly Order $order,
        private readonly Cart $cart,
        private readonly StoreContext $storeContext,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
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
