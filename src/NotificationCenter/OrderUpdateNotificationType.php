<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\NotificationCenter;

use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

/**
 * @internal
 */
class OrderUpdateNotificationType extends OrderNotificationType
{
    public const NAME = 'iso_order_update';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            ...parent::getTokenDefinitions(),
            $this->create(TextTokenDefinition::class, 'previous_order_status'),
            $this->create(AnythingTokenDefinition::class, 'previous_order_status_*'), // All the order status model fields
            $this->create(TextTokenDefinition::class, 'previous_transaction_status'),
            $this->create(AnythingTokenDefinition::class, 'previous_transaction_status_*'), // All the order status model fields
            $this->create(TextTokenDefinition::class, 'previous_delivery_status'),
            $this->create(AnythingTokenDefinition::class, 'previous_delivery_status_*'), // All the order status model fields
            $this->create(TextTokenDefinition::class, 'tracking_numbers'),
            $this->create(TextTokenDefinition::class, 'customer_notes'),
        ];
    }
}
