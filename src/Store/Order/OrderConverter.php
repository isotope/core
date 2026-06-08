<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\CartConvertedEvent;
use Isotope\CoreBundle\ProductCollection\Event\LineItemConvertedEvent;
use Isotope\CoreBundle\ProductCollection\Event\OrderConvertedEvent;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\Transformer\AddressTransformer;
use Isotope\CoreBundle\Store\Order\Transformer\CartTransformer;
use Isotope\CoreBundle\Store\Order\Transformer\ExtensionTransformer;
use Isotope\CoreBundle\Store\Order\Transformer\LineItemTransformer;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @experimental
 */
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
        $data['line_items'] = $this->convertLineItems($cart, $context);

        if ($shippingAddress = $cart->getShippingAddress()) {
            $data['shipping_address'] = AddressTransformer::transform($shippingAddress);
        }

        if ($extensions = $cart->getExtensions()) {
            $data['extensions'] = ExtensionTransformer::transform($extensions);
        }

        $event = new CartConvertedEvent($cart, $data, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->getConvertedCart();
    }

    public function convertToCart(Order $order, StoreContext $context): Cart
    {
        // TODO: do we need to clone things?
        $cart = new Cart($order->getIdentifier());
        $cart->setBillingAddress($order->getBillingAddress());
        $cart->setShippingAddress($order->getShippingAddress());
        $cart->setPaymentMethod($order->getPaymentMethod());
        $cart->setShippingMethod($order->getShippingMethod());
        $cart->setLineItems($order->getLineItems()->products());
        $cart->setSubtotals($order->getSubtotals());
        $cart->setTotal($order->getTotal());
        $cart->setExtensions($order->getExtensions());
        $cart->setId(0); // TODO: should contain the orders' source_collection_id

        $event = new OrderConvertedEvent($order, $cart, $context);
        $this->eventDispatcher->dispatch($event);

        return $cart;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function convertLineItems(Cart $cart, StoreContext $context): array
    {
        $items = [];
        $position = 1;

        foreach ($cart->getLineItems()->byLevel() as $level => $lineItems) {
            foreach ($lineItems as $lineItem) {
                $data = LineItemTransformer::transform($lineItem, $position);

                $event = new LineItemConvertedEvent($lineItem, $data, $cart, $context);
                $this->eventDispatcher->dispatch($event);

                $items[] = $event->getConvertedLineItem();
                $position++;
            }

            $items[] = LineItemTransformer::transformSubtotal($level, $cart->getSubtotal($level), $position);
        }

        $items[] = [
            'type' => LineItem::TYPE_TOTAL,
            'sorting' => $position,
            'quantity' => 1,
            'price' => $cart->getTotal()?->getTotalPrice() ?? 0.0,
            'tax_free_price' => 0,
            'price_data' => $cart->getTotal()?->jsonSerialize(),
            'tax_id' => '',
        ];

        return $items;
    }
}
