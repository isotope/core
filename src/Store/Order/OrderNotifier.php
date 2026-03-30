<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Store\Event\CollectOrderNotificationTokensEvent;
use Isotope\CoreBundle\Store\Exception\CollectionTypeException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Receipt\ReceiptCollection;

class OrderNotifier
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NotificationCenter $notificationCenter,
        private readonly OrderFactory $orderFactory,
        private readonly ConfigLoader $notificationConfigLoader,
    ) {
    }

    public function sendNotification(int $orderId, int $notificationId, array $tokens = []): ReceiptCollection
    {
        $order = $this->orderFactory->loadFromId($orderId);

        if (!$order) {
            throw new CollectionTypeException(Order::getType(), null);
        }

        $tokens += $this->getTokens($order, $notificationId);

        return $this->notificationCenter->sendNotification($notificationId, $tokens, $order->getContext()->getLocale());
    }

    public function getTokens(Order $order, int $notificationId): array
    {
        $notificationConfig = $this->notificationConfigLoader->loadNotification($notificationId);

        if (!$notificationConfig) {
            throw CouldNotCreateParcelException::becauseOfNonExistentNotification($notificationId);
        }

        $event = new CollectOrderNotificationTokensEvent($order, $notificationConfig);

        $this->eventDispatcher->dispatch($event);

        return $event->getTokens();
    }
}
