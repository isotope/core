<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration\EventListener;

use Contao\StringUtil;
use Isotope\CoreBundle\Migration\Event\ModuleToElementMigrationEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OrderConditionListener
{
    public function __invoke(ModuleToElementMigrationEvent $event): void
    {
        if ('checkout' !== $event->data['type'] || empty($event->data['iso_order_conditions'])) {
            return;
        }

        $orderConditions = StringUtil::deserialize($event->data['iso_order_conditions']);

        if (!\is_array($orderConditions)) {
            return;
        }

        foreach ($orderConditions as &$condition) {
            $condition['step'] = match($condition['step']) {
                'BillingAddress',
                'ShippingAddress' => 'address',
                'ShippingMethod' => 'shipping',
                'PaymentMethod' => 'payment',
                'OrderInfo',
                'OrderProducts' => '_summary',
                default => $condition['step'],
            };
        }

        unset($condition);

        $event->jsonData['iso_order_conditions'] = serialize($orderConditions);
    }
}
