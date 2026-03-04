<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\CartConvertedEvent;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\Transformer\AddressTransformer;
use Isotope\CoreBundle\Store\Order\Transformer\CartTransformer;
use Isotope\CoreBundle\Store\Order\Transformer\LineItemTransformer;
use Psr\EventDispatcher\EventDispatcherInterface;

class OrderConverter
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function convertToOrder(Cart $cart, StoreContext $context): array
    {
        $data = CartTransformer::transform($cart, $context);

        $data['billing_address'] = AddressTransformer::transform($cart->getBillingAddress());
        $data['line_items'] = LineItemTransformer::transformCollection($cart->getLineItems());

        if ($shippingAddress = $cart->getShippingAddress()) {
            $data['shipping_address'] = AddressTransformer::transform($shippingAddress);
        }

        $event = new CartConvertedEvent($cart, $data, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->getConvertedCart();
    }
}
