<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\Model\ProductCollectionItem;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\CartCreatedEvent;
use Isotope\CoreBundle\Store\Event\CartItemLoadedEvent;
use Isotope\Model\Address;
use Psr\EventDispatcher\EventDispatcherInterface;

class CartFactory
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PaymentRegistry $paymentRegistry,
        private readonly ShippingRegistry $shippingRegistry,
    ) {
    }

    public function create(StoreContext $context, ProductCollection|null $cartModel = null): Cart
    {
        if (null !== $cartModel && Cart::COLLECTION_TYPE !== $cartModel->type) {
            throw new \InvalidArgumentException('Invalid collection model type to create a cart.');
        }

        $cart = new Cart($context->getIdentifier());

        $this->addMetadata($cart, $cartModel, $context);

        if ($cartModel) {
            $this->loadItems($cart, $cartModel, $context);
        }

        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart, $context, $cartModel));

        // TODO: add collection errors, e.g. if minimum subtotal is not reached

        return $cart;
    }

    private function addMetadata(Cart $cart, ProductCollection|null $cartModel, StoreContext $context): void
    {
        $cart->setBillingAddress(fn () => $this->loadAddress((int) $cartModel?->billing_address_id, $context->getDefaultBillingAddress()));
        $cart->setShippingAddress(fn () => $this->loadAddress((int) $cartModel?->shipping_address_id, $context->getDefaultShippingAddress()));

        if ($cartModel?->payment_id) {
            $cart->setPaymentMethod(fn () => $this->paymentRegistry->get(Payment::findById($cartModel->payment_id)));
        }

        if ($cartModel?->shipping_id) {
            $cart->setShippingMethod(fn () => $this->shippingRegistry->get(Shipping::findById($cartModel->shipping_id)));
        }

        // TODO: more?
    }

    private function loadAddress(int $id, Address|null $default = null): Address|null
    {
        if (!$id) {
            return $default;
        }

        return Address::findById($id) ?? $default;
    }

    private function loadItems(Cart $cart, ProductCollection $cartModel, StoreContext $context): void
    {
        $models = ProductCollectionItem::findByCollection($cartModel->id)?->getModels() ?? [];

        foreach ($models as $model) {
            $item = new LineItem(
                $model->type,
                $model->identifier,
                (int) $model->quantity,
            );

            $item->setId($model->id);
            $item->setSku($model->sku);
            $item->setLabel($model->name);

            if (\is_array($configuration = StringUtil::deserialize($model->configuration))) {
                $item->setConfiguration($configuration);
            }

            $event = new CartItemLoadedEvent($item, $cart, $context);
            $this->eventDispatcher->dispatch($event);

            if (!$event->isSkipped()) {
                $cart->add($item);
            }
        }
    }
}
