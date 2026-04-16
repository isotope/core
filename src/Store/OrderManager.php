<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Isotope\CoreBundle\EventDispatcher\TransactionDispatcher;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Store\Event\OrderTransaction;
use Isotope\CoreBundle\Util\StringUtil;
use Isotope\CoreBundle\Validator\Exception\ConstraintViolationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderManager
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TransactionDispatcher $transactionDispatcher,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function createOrder(array $data, StoreContext $context): void
    {
        $this->validateOrderData($data, $context);

        $this->transactionDispatcher->dispatch(
            new OrderTransaction($data, $context),
            $this->place(...),
        );
    }

    private function validateOrderData(array $data, StoreContext $context): void
    {
        $validationEvent = new OrderValidationEvent($data, $context);
        $this->eventDispatcher->dispatch($validationEvent, 'isotope.validation.order.create');

        $violations = $this->validator->validate($data, $validationEvent->getConstraints());

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $data);
        }
    }

    private function place(array $data, StoreContext $context)
    {
        $billingAddress = $this->saveAddress($data['billing_address'], $context->getStoreId());
        $shippingAddress = $this->saveAddress($data['shipping_address'] ?? null, $context->getStoreId());

        $collection = new ProductCollection();
        $collection->tstamp = time();
        $collection->type = Order::getType();
        $collection->uniqid = StringUtil::generateRandomId();
        $collection->member = $data['member_id'];
        $collection->store_id = $data['store_id'];
        $collection->config_id = $data['config_id'];
        $collection->payment_id = $data['payment_id'];
        $collection->shipping_id = $data['shipping_id'];
        $collection->billing_address_id = $billingAddress->id ?? 0;
        $collection->shipping_address_id = $shippingAddress?->id ?? 0;
        $collection->currency = $data['currency'];
        $collection->language = $data['locale'];

        $collection->save();

        foreach ($data['line_items'] as $lineItem) {
            $item = new ProductCollectionItem();
            $item->setRow($lineItem);
            $item->tstamp = time();
            $item->save();
        }

        // TODO: save order status

        $billingAddress->pid = $collection->id;
        $billingAddress->save();

        if ($shippingAddress) {
            $shippingAddress->pid = $collection->id;
            $shippingAddress->save();
        }
    }

    private function saveAddress(array|null $data, int $storeId): Address|null
    {
        if (null === $data) {
            return null;
        }

        unset($data['id']);

        $address = new AddressModel();
        $address->setRow($data);
        $address->pid = 0;
        $address->ptable = 'tl_iso_product_collection';
        $address->tstamp = time();
        $address->store_id = $storeId;

        $address->save();

        return $address;
    }
}
