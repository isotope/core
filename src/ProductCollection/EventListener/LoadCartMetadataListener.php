<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\LoadCollectionTransaction;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(LoadCollectionTransaction::NAME.'.post')]
class LoadCartMetadataListener
{
    public function __construct(
        private readonly PaymentRegistry $paymentRegistry,
        private readonly ShippingRegistry $shippingRegistry,
    ) {
    }

    public function __invoke(LoadCollectionTransaction $event): void
    {
        $cart = $event->collection;
        $cartModel = $event->collectionModel;
        $context = $event->context;

        if (!$cart instanceof Cart) {
            return;
        }

        $cart->setBillingAddress(fn () => $this->loadAddress((int) $cartModel?->billing_address_id, $context->getDefaultBillingAddress()));
        $cart->setShippingAddress(fn () => $this->loadAddress((int) $cartModel?->shipping_address_id));

        if ($paymentId = $cartModel?->payment_id) {
            $cart->setPaymentMethod(fn () => $this->paymentRegistry->get(Payment::findById($paymentId), $context));
        }

        if ($shippingId = $cartModel?->shipping_id) {
            $cart->setShippingMethod(fn () => $this->shippingRegistry->get(Shipping::findById($shippingId), $context));
        }
    }

    private function loadAddress(int $id, Address|null $default = null): Address|null
    {
        if (!$id) {
            return $default;
        }

        return Address::findById($id) ?? $default;
    }
}
