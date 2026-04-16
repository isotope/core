<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\Model\ShippingModel;
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
            $cart->setPaymentMethod(fn () => $this->paymentRegistry->get(PaymentModel::findById($paymentId), $context));
        }

        if ($shippingId = $cartModel?->shipping_id) {
            $cart->setShippingMethod(fn () => $this->shippingRegistry->get(ShippingModel::findById($shippingId), $context));
        }
    }

    private function loadAddress(int $id, AddressModel|null $default = null): AddressModel|null
    {
        if (!$id) {
            return $default;
        }

        return AddressModel::findById($id) ?? $default;
    }
}
