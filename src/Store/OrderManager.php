<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Contao\BackendUser;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\HttpFoundation\PaymentResult;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\Model\ProductCollectionLogModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\OrderTransaction\OrderTransactionHandler;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\DocumentNumberTransactionEvent;
use Isotope\CoreBundle\Store\Event\OrderStatusTransactionEvent;
use Isotope\CoreBundle\Store\Event\OrderTransactionEvent;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Store\Exception\CollectionTypeException;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use Isotope\CoreBundle\Store\Order\TransactionState;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use Isotope\CoreBundle\Util\StringUtil;
use Isotope\CoreBundle\Validator\Exception\ConstraintViolationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @experimental
 */
class OrderManager
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TransactionEventDispatcher $transactionDispatcher,
        private readonly ValidatorInterface $validator,
        private readonly Connection $connection,
        private readonly InsertTagParser $insertTagParser,
        private readonly WorkflowManager $workflowManager,
        private readonly OrderTransactionHandler $orderTransactionHandler,
        private readonly Security $security,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @throws ConstraintViolationException
     */
    public function createOrder(array $data, StoreContext $context): CreateOrderResult
    {
        $this->validateOrderData($data, $context);

        return $this->transactionDispatcher->dispatch(
            new OrderTransactionEvent($data, $context),
            function (OrderTransactionEvent $event): CreateOrderResult {
                $data = $event->getData();
                $context = $event->getStoreContext();

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
                $collection->extensions = isset($data['extensions']) ? json_encode($data['extensions'], JSON_THROW_ON_ERROR) : null;

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

                unset($lineItem);

                $collection->order_status = array_key_first($this->workflowManager->getOrderWorkflow()->getMarking($collection)->getPlaces());
                $collection->transaction_status = array_key_first($this->workflowManager->getTransactionWorkflow()->getMarking($collection)->getPlaces());
                $collection->delivery_status = array_key_first($this->workflowManager->getDeliveryWorkflow()->getMarking($collection)->getPlaces());
                $collection->billing_address_id = $billingAddress->id ?? 0;
                $collection->shipping_address_id = $shippingAddress->id ?? 0;
                $collection->save();

                $event->setData($data);

                return new CreateOrderResult((int) $collection->id, $collection->uniqid);
            },
        );
    }

    public function lockOrder(int $orderId, StoreContext $context, \DateTimeInterface|null $locked = null): bool
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
                $documentNumber = $this->transactionDispatcher->dispatch(
                    new DocumentNumberTransactionEvent($context->getDocumentNumberPrefix(), $context->getDocumentNumberDigits(), $context->getStoreId()),
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
    public function updateOrder(int $orderId, array $values/* , bool $updateDatabase = true */): array
    {
        $updateDatabase = (bool) (\func_get_args()[2] ?? true);
        $order = $this->getOrderModel($orderId);

        return $this->transactionDispatcher->dispatch(
            new OrderStatusTransactionEvent($order, $values),
            function (OrderStatusTransactionEvent $event) use ($updateDatabase): array {
                $order = $event->getOrder();
                $values = $event->getValues();

                foreach ($values as $k => $v) {
                    if ($v === $order->{$k}) {
                        unset($values[$k]);
                    }
                }

                if ([] === $values) {
                    return [];
                }

                if (
                    isset($values['order_status'])
                    && $values['order_status'] !== $order->getOrderStatus()
                ) {
                    if ($this->workflowManager->getOrderWorkflow()->can($order, $values['order_status'])) {
                        $this->workflowManager->getOrderWorkflow()->apply($order, $values['order_status']);
                        $values['order_status'] = $order->getOrderStatus();
                    } else {
                        unset($values['order_status']);
                    }
                }

                if (
                    isset($values['transaction_status'])
                    && $values['transaction_status'] !== $order->getTransactionStatus()
                ) {
                    if ($this->workflowManager->getTransactionWorkflow()->can($order, $values['transaction_status'])) {
                        $this->workflowManager->getTransactionWorkflow()->apply($order, $values['transaction_status']);
                        $values['transaction_status'] = $order->getTransactionStatus();
                    } else {
                        unset($values['transaction_status']);
                    }
                }

                if (
                    isset($values['delivery_status'])
                    && $values['delivery_status'] !== $order->getDeliveryStatus()
                ) {
                    if ($this->workflowManager->getDeliveryWorkflow()->can($order, $values['delivery_status'])) {
                        $this->workflowManager->getDeliveryWorkflow()->apply($order, $values['delivery_status']);
                        $values['delivery_status'] = $order->getDeliveryStatus();
                    } else {
                        unset($values['delivery_status']);
                    }
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

    public function updatePayment(int $orderId, array $data, StoreContext $context): array
    {
        $this->validateOrderData($data, $context);

        if (isset($data['line_items'])) {
            $this->connection->executeStatement('DELETE FROM tl_iso_product_collection_item WHERE pid=? AND type!=?', [$orderId, LineItem::TYPE_PRODUCT]);

            foreach ($data['line_items'] as &$lineItem) {
                if (LineItem::TYPE_PRODUCT === $lineItem['type']) {
                    continue;
                }

                $item = new ProductCollectionItemModel();
                $item->setRow($lineItem);
                $item->pid = $orderId;
                $item->tstamp = time();
                $item->price_data = json_encode($item->price_data);
                $item->save();

                $lineItem['id'] = $item->id;
            }

            unset($lineItem);
        }

        return array_merge($data, $this->updateOrder($orderId, [
            'payment_id' => $data['payment_id'],
            'subtotal' => $data['subtotal'],
            'total' => $data['total'],
            'transaction_status' => array_key_first($this->workflowManager->getTransactionWorkflow()->getMarking(new ProductCollectionModel())->getPlaces()),
        ]));
    }

    public function initPayment(Order $order, PaymentInterface $payment, PageModel $pageModel): PaymentResult
    {
        $transactionModel = $this->orderTransactionHandler->init($order);

        $returnUrl = $this->urlGenerator->generate($pageModel, ['parameters' => '/'.$order->getIdentifier(), 'transaction' => $transactionModel->uniqid, 'transaction_state' => 'success'], UrlGeneratorInterface::ABSOLUTE_URL);
        $errorUrl = $this->urlGenerator->generate($pageModel, ['parameters' => '/'.$order->getIdentifier(), 'transaction' => $transactionModel->uniqid, 'transaction_state' => 'failed'], UrlGeneratorInterface::ABSOLUTE_URL);

        return $payment->process($order, $transactionModel, $returnUrl, $errorUrl);
    }

    public function canCancelPayment(Order $order, string $status): bool
    {
        if (TransactionState::Pending !== $order->getTransactionStatus()?->getTransactionState()) {
            return false;
        }

        $workflow = $this->workflowManager->getTransactionWorkflow();
        $model = $this->getOrderModel($order->getId());

        return $workflow->can($model, $status);
    }

    public function cancelPayment(Order $order, string $status): bool
    {
        if (!$this->canCancelPayment($order, $status)) {
            return false;
        }

        $values = $this->updateOrder($order->getId(), ['transaction_status' => $status]);

        if (!isset($values['transaction_status'])) {
            return false;
        }

        $this->orderTransactionHandler->cancel($order);

        return true;
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

    private function saveAddress(array $data, int $pid, int $storeId): AddressModel
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

    private function generateDocumentNumber(DocumentNumberTransactionEvent $event): string
    {
        $prefix = $this->insertTagParser->replaceInline($event->getPrefix());

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('document_number')
            ->from('tl_iso_product_collection')
            ->where('type=:type')
            ->andWhere('store_id=:storeId')
            ->setParameter('type', Order::getType())
            ->setParameter('storeId', $event->getStoreId())
            ->orderBy('document_number', 'DESC')
        ;

        if ('' !== $prefix) {
            $qb
                ->andWhere('document_number LIKE :prefix')
                ->setParameter('prefix', $prefix.'%')
            ;
        }

        $result = $qb->fetchOne();

        if (!\is_string($result)) {
            $max = 0;
        } else {
            $max = (int) substr($result, (new UnicodeString($prefix))->length());
        }

        return $prefix.str_pad((string) ($max + 1), $event->getLength(), '0', STR_PAD_LEFT);
    }

    private function getOrderModel(int $id): ProductCollectionModel
    {
        $order = ProductCollectionModel::findById($id);

        if (Order::getType() !== $order?->type) {
            throw new CollectionTypeException(Order::getType(), $order);
        }

        return $order;
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
