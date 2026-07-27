<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\OrderTransaction;

use Isotope\CoreBundle\Model\OrderTransactionEventModel;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Symfony\Component\Lock\LockFactory;

/**
 * @internal
 */
class OrderTransactionHandler
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly LockFactory $lockFactory,
    ) {
    }

    public function init(Order $order): OrderTransactionModel
    {
        if ($models = OrderTransactionModel::findByOrderId($order->getId())) {
            foreach ($models as $model) {
                if (!$model->status) {
                    $model->status = 'cancelled';
                    $model->save();
                }
            }
        }

        $transactionModel = OrderTransactionModel::createForOrder($order);
        $transactionModel->save();

        return $transactionModel;
    }

    public function switch(Order $order, string|null $uuid): OrderTransactionModel
    {
        $result = null;

        if ($models = OrderTransactionModel::findByOrderId($order->getId())) {
            foreach ($models as $model) {
                if ($model->uniqid === $uuid) {
                    $result = $model;
                    continue;
                }

                if (!$model->status) {
                    $model->status = 'cancelled';
                    $model->save();
                }
            }
        }

        if (!$result) {
            $result = OrderTransactionModel::createForOrder($order);
            $result->save();
        }

        return $result;
    }

    public function cancel(Order $order): void
    {
        if ($models = OrderTransactionModel::findByOrderId($order->getId())) {
            foreach ($models as $model) {
                if (!$model->status) {
                    $model->status = 'cancelled';
                    $model->save();
                }
            }
        }
    }

    /**
     * @param callable(Order, OrderTransactionModel, OrderTransactionEventModel): void|null $callback
     */
    public function handleEvent(OrderTransactionModel|null $transactionModel, array|string $payload, callable|null $callback = null): bool
    {
        if (!$transactionModel) {
            return false;
        }

        $eventModel = new OrderTransactionEventModel();
        $eventModel->pid = $transactionModel->id;
        $eventModel->tstamp = time();
        $eventModel->payload = \is_array($payload) ? json_encode($payload) : $payload;
        $eventModel->save();

        if ($callback) {
            $lock = $this->lockFactory->createLock('isotope-transaction-'.$transactionModel->pid);

            try {
                $lock->acquire(true);

                $order = $this->orderFactory->loadFromId((int) $transactionModel->pid);

                if (!$order) {
                    return false;
                }

                $callback($order, $transactionModel, $eventModel);
            } finally {
                $lock->release();
            }
        }

        return true;
    }
}
