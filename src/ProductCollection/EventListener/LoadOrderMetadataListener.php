<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\OrderStatusModel;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\LoadCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Order\StatusType;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Util\FloatComparator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(LoadCollectionTransaction::NAME.'.post')]
class LoadOrderMetadataListener
{
    public function __construct(
        private readonly PaymentRegistry $paymentRegistry,
        private readonly ShippingRegistry $shippingRegistry,
    ) {
    }

    public function __invoke(LoadCollectionTransaction $event): void
    {
        $order = $event->collection;
        $model = $event->collectionModel;
        $context = $event->context;

        if (!$order instanceof Order) {
            return;
        }

        if ($model->document_number) {
            $order->setDocumentNumber($model->document_number);
        }

        if ($model->order_status) {
            $order->setOrderStatus(static fn () => OrderStatusModel::findOneByAlias($model->order_status, StatusType::Order));
        }

        if ($model->transaction_status) {
            $order->setTransactionStatus(static fn () => OrderStatusModel::findOneByAlias($model->transaction_status, StatusType::Transaction));
        }

        if ($model->delivery_status) {
            $order->setDeliveryStatus(static fn () => OrderStatusModel::findOneByAlias($model->delivery_status, StatusType::Delivery));
        }

        if ($model->locked) {
            $order->setLocked(\DateTimeImmutable::createFromFormat('U', (string) $model->locked));
        }

        $order->setBillingAddress(fn () => AddressModel::findById($model->billing_address_id));
        $order->setShippingAddress(fn () => AddressModel::findById($model->shipping_address_id));

        if ($paymentId = $model?->payment_id) {
            $order->setPaymentMethod(fn () => $this->paymentRegistry->get(PaymentModel::findById($paymentId), $context));
        }

        if ($shippingId = $model?->shipping_id) {
            $order->setShippingMethod(fn () => $this->shippingRegistry->get(ShippingModel::findById($shippingId), $context));
        }
    }
}
