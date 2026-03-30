<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\NotificationCenter\OrderNotificationType;
use Isotope\CoreBundle\NotificationCenter\OrderUpdateNotificationType;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class NotificationOptionsListener
{
    public function __construct(private readonly NotificationCenter $notificationCenter)
    {
    }

    #[AsCallback('tl_content', 'fields.iso_notifications.options')]
    public function getOrderNotifications(): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType(OrderNotificationType::NAME);
    }

    #[AsCallback('tl_iso_product_collection', 'fields.notification.options')]
    public function getOrderUpdateNotifications(): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType(OrderUpdateNotificationType::NAME);
    }
}
