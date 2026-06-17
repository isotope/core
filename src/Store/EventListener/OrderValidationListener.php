<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\EventListener;

use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Validator\Constraints\NotHasKey;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
#[AsEventListener('isotope.validation.order.create')]
class OrderValidationListener
{
    public function __construct(private readonly AddressValidator $addressValidator)
    {
    }

    public function __invoke(OrderValidationEvent $event): void
    {
        $context = $event->getStoreContext();

        $event->addConstraint(new Assert\Collection(
            fields: [
                'member_id' => new Assert\Required([new Assert\Positive()]),
                'config_id' => [new Assert\NotBlank(), new Assert\Positive()],
                'store_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'locale' => [new Assert\NotBlank(), new Assert\Locale()],
                'source_collection_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],

                'billing_address' => $this->addressValidator->getConstraint($context->getBillingAddressConfig()),
                'shipping_address' => new Assert\Optional($this->addressValidator->getConstraint($context->getShippingAddressConfig())),

                'payment_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'shipping_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],

                'subtotal' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'total' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'currency' => [new Assert\NotBlank(), new Assert\Currency()],

                'order_status' => new Assert\Optional([new Assert\NotBlank(), new Assert\Positive()]),
                'date_paid' => new Assert\Optional([new Assert\NotBlank(), new Assert\DateTime()]),
                'date_shipped' => new Assert\Optional([new Assert\NotBlank(), new Assert\DateTime()]),

                'extensions' => new Assert\Optional([new Assert\Type('array')]),
            ],
            allowExtraFields: true,
        ));

        $event->addConstraint(new NotHasKey('notes'));
    }
}
