<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Contao\BackendUser;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\EventDispatcher\TransactionDispatcher;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\Model\ProductCollectionLogModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\DocumentNumberTransaction;
use Isotope\CoreBundle\Store\Event\OrderStatusTransaction;
use Isotope\CoreBundle\Store\Event\OrderTransaction;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Store\Exception\CollectionTypeException;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use Isotope\CoreBundle\Util\StringUtil;
use Isotope\CoreBundle\Validator\Exception\ConstraintViolationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @experimental
 */
class OrderManager
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TransactionDispatcher $transactionDispatcher,
        private readonly ValidatorInterface $validator,
        private readonly Connection $connection,
        private readonly InsertTagParser $insertTagParser,
        private readonly WorkflowManager $workflowManager,
        private readonly Security $security,
    ) {
    }

    /**
     * @throws ConstraintViolationException
     */
    public function createOrder(array $data, StoreContext $context): CreateOrderResult
    {
        $this->validateOrderData($data, $context);

        return $this->transactionDispatcher->dispatch(
            new OrderTransaction($data, $context),
            function (OrderTransaction $transaction): CreateOrderResult {
                $data = $transaction->getData();
                $context = $transaction->getStoreContext();

                $collection = new ProductCollectionModel();
                $collection->tstamp = time();
                $collection->type = Order::getType();
                $collection->uniqid = StringUtil::generateRandomId();
                $collection->member = $data['member_id'];
                $collection->config_id = $data['config_id'];
                $collection->store_id = $data['store_id'];
                $collection->language = $data['locale'];
                $collection->source_collection_id = $data['source_collection_id'];
                $collection->payment_id = $data['payment_id'];
                $collection->shipping_id = $data['shipping_id'];
                $collection->subtotal = $data['subtotal'];
                $collection->total = $data['total'];
                $collection->currency = $data['currency'];

                $collection->save();

                $billingAddress = $this->saveAddress($data['billing_address'], (int) $collection->id, $context->getStoreId());
                $shippingAddress = null;

                if (isset($data['shipping_address'])) {
                    $shippingAddress = $this->saveAddress($data['shipping_address'], (int) $collection->id, $context->getStoreId());
                }

                foreach ($data['line_items'] as &$lineItem) {
                    $item = new ProductCollectionItemModel();
                    $item->setRow($lineItem);
                    $item->pid = $collection->id;
                    $item->tstamp = time();
                    $item->price_data = json_encode($item->price_data);
                    $item->save();

                    $lineItem['id'] = $item->id;
                }

                unset ($lineItem);

                $collection->order_status = array_key_first($this->workflowManager->getOrderWorkflow()->getMarking($collection)->getPlaces());
                $collection->transaction_status = array_key_first($this->workflowManager->getTransactionWorkflow()->getMarking($collection)->getPlaces());
                $collection->delivery_status = array_key_first($this->workflowManager->getDeliveryWorkflow()->getMarking($collection)->getPlaces());
                $collection->billing_address_id = $billingAddress->id ?? 0;
                $collection->shipping_address_id = $shippingAddress?->id ?? 0;
                $collection->save();

                $transaction->setData($data);

                return new CreateOrderResult((int) $collection->id, $collection->uniqid);
            },
        );
    }

    public function lockOrder(int $orderId, StoreContext $context, \DateTimeInterface $locked = null): bool
    {
        $collection = ProductCollectionModel::findById($orderId);

        if (Order::getType() !== $collection?->type) {
            throw new CollectionTypeException(Order::getType(), $collection);
        }

        if ($collection->locked) {
            return false;
        }

        try {
            $this->connection->executeStatement('LOCK TABLES tl_iso_product_collection WRITE');

            $locked ??= new \DateTimeImmutable();
            $documentNumber = $collection->document_number;

            if (!$documentNumber) {
                // TODO: load from store config / context
                $prefix = '';
                $length = 6;
                $documentNumber = $this->transactionDispatcher->dispatch(
                    new DocumentNumberTransaction($prefix, $length, $context->getStoreId()),
                    $this->generateDocumentNumber(...),
                );
            }

            $this->connection->executeStatement(
                'UPDATE tl_iso_product_collection SET locked=?, document_number=? WHERE id=?',
                [$locked->getTimestamp(), $documentNumber, $orderId],
            );

            $collection->refresh();
        } finally {
            $this->connection->executeStatement('UNLOCK TABLES');
        }

        return true;
    }

    /**
     * Updates the order status and writes changes to the order log.
     *
     * Third argument $updateDatabase is @internal only for use in the DCA onbeforesubmit callback.
     */
    public function updateOrder(int $orderId, array $values/*, bool $updateDatabase = true*/): array
    {
        $updateDatabase = (bool) (func_get_args()[2] ?? true);
        $order = ProductCollectionModel::findById($orderId);

        if (Order::getType() !== $order?->type) {
            throw new CollectionTypeException(Order::getType(), $order);
        }

        return $this->transactionDispatcher->dispatch(
            new OrderStatusTransaction($order, $values),
            function (OrderStatusTransaction $transaction) use ($updateDatabase): array {
                $order = $transaction->getOrder();
                $values = $transaction->getValues();

                $hasChanges = false;
                foreach ($values as $k => $v) {
                    if ($hasChanges = ($v !== $order->{$k})) {
                        break;
                    }
                }

                if (!$hasChanges) {
                    return $values;
                }

                if (isset($values['order_status'])) {
                    $this->workflowManager->getOrderWorkflow()->apply($order, $values['order_status']);
                    $values['order_status'] = $order->getOrderStatus();
                }

                if (isset($values['transaction_status'])) {
                    $this->workflowManager->getTransactionWorkflow()->apply($order, $values['transaction_status']);
                    $values['transaction_status'] = $order->getTransactionStatus();
                }

                if (isset($values['delivery_status'])) {
                    $this->workflowManager->getDeliveryWorkflow()->apply($order, $values['delivery_status']);
                    $values['delivery_status'] = $order->getDeliveryStatus();
                }

                $log = new ProductCollectionLogModel();
                $log->pid = $order->id;
                $log->tstamp = time();
                $log->author = $this->getUserId();
                $log->setData($values);
                $log->save();

                if ($updateDatabase) {
                    foreach ($values as $field => $value) {
                        $order->$field = $value;
                    }

                    $order->save();
                }

                return $values;
            },
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

    private function saveAddress(array $data, int $pid, int $storeId): AddressModel|null
    {
        unset($data['id']);

        $address = new AddressModel();
        $address->setRow($data);
        $address->pid = $pid;
        $address->ptable = 'tl_iso_product_collection';
        $address->tstamp = time();
        $address->store_id = $storeId;

        $address->save();

        return $address;
    }

    private function generateDocumentNumber(DocumentNumberTransaction $transaction): string
    {
        $prefix = $this->insertTagParser->replaceInline($transaction->getPrefix());

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('document_number')
            ->from('tl_iso_product_collection')
            ->where('type=:type')
            ->andWhere('store_id=:storeId')
            ->setParameter('type', Order::getType())
            ->setParameter('storeId', $transaction->getStoreId())
            ->orderBy('document_number', 'DESC')
        ;

        if ('' !== $prefix) {
            $qb
                ->andWhere('document_number LIKE :prefix')
                ->setParameter('prefix', $prefix.'%')
            ;
        }

        $result = $qb->fetchOne();

        if (false === $result) {
            $max = 0;
        } else {
            $max = (int) substr($result, mb_strlen($prefix));
        }

        return $prefix.str_pad((string) ($max + 1), $transaction->getLength(), '0', STR_PAD_LEFT);
    }

    private function getUserId(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            return 0;
        }

        return (int) $user->id;
    }
}
