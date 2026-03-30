<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\NotificationCenter;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

/**
 * @internal
 */
class OrderNotificationType implements NotificationTypeInterface
{
    public const NAME = 'iso_order';

    public function __construct(
        private readonly TokenDefinitionFactoryInterface $factory,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->create(TextTokenDefinition::class, 'uniqid'),
            $this->create(TextTokenDefinition::class, 'order_status'),
            $this->create(AnythingTokenDefinition::class, 'order_status_*'), // All the order status model fields
            $this->create(TextTokenDefinition::class, 'transaction_status'),
            $this->create(AnythingTokenDefinition::class, 'transaction_status_*'), // All the order status model fields
            $this->create(TextTokenDefinition::class, 'delivery_status'),
            $this->create(AnythingTokenDefinition::class, 'delivery_status_*'), // All the order status model fields
            $this->create(EmailTokenDefinition::class, 'recipient_email'),
            $this->create(TextTokenDefinition::class, 'order_id'),
            $this->create(TextTokenDefinition::class, 'order_items'),
            $this->create(TextTokenDefinition::class, 'order_products'),
            $this->create(TextTokenDefinition::class, 'order_subtotal'),
            $this->create(TextTokenDefinition::class, 'order_total'),
            $this->create(TextTokenDefinition::class, 'document_number'),
            $this->create(HtmlTokenDefinition::class, 'cart_html'),
            $this->create(TextTokenDefinition::class, 'cart_text'),
            $this->create(TextTokenDefinition::class, 'document'),
            $this->create(TextTokenDefinition::class, 'bank_name'),
            $this->create(TextTokenDefinition::class, 'bank_account'),
            $this->create(TextTokenDefinition::class, 'bank_code'),
            $this->create(TextTokenDefinition::class, 'tax_number'),
            //'collection_*', // All the collection fields
            $this->create(TextTokenDefinition::class, 'billing_address'), // Formatted billing address
            $this->create(AnythingTokenDefinition::class, 'billing_address_*'), // All the billing address model fields
            $this->create(EmailTokenDefinition::class, 'billing_address_email'),
            $this->create(TextTokenDefinition::class, 'shipping_address'), // Formatted shipping address
            $this->create(AnythingTokenDefinition::class, 'shipping_address_*'), // All the shipping address model fields
            $this->create(EmailTokenDefinition::class, 'shipping_address_email'),
            //'form_*', // All the order condition form fields
            //'payment_id', // Payment method ID
            //'payment_label', // Payment method label
            //'payment_note', // Payment method note
            //'payment_*',
            //'shipping_id', // Shipping method ID
            //'shipping_label', // Shipping method label
            //'shipping_note', // Shipping method note
            //'shipping_*',
            $this->create(AnythingTokenDefinition::class, 'config_*'), // Store configuration model fields
            $this->create(EmailTokenDefinition::class, 'config_email'),
            $this->create(AnythingTokenDefinition::class, 'member_*'), // Member model fields
        ];
    }

    protected function create(string $class, string $name): TokenDefinitionInterface
    {
        return $this->factory->create($class, $name, 'iso_order.'.$name);
    }
}
