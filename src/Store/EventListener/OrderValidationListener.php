<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\EventListener;

use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Validator\Constraints\NotHasKey;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Validator\Constraints as Assert;

#[AsEventListener('isotope.validation.order.create')]
class OrderValidationListener
{
    public function __construct(
        private readonly AddressValidator $addressValidator,
    ) {
    }

    public function __invoke(OrderValidationEvent $event): void
    {
        $context = $event->getStoreContext();

        $event->addConstraint(new Assert\Collection(
            fields: [
                'member_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'store_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'config_id' => [new Assert\NotBlank(), new Assert\Positive()],

                'billing_address' => $this->addressValidator->getConstraint($context->getBillingAddressConfig()),
                'shipping_address' => new Assert\Optional($this->addressValidator->getConstraint($context->getShippingAddressConfig())),
                'payment_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'shipping_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                'currency' => [new Assert\NotBlank(), new Assert\Currency()],
                'locale' => [new Assert\NotBlank(), new Assert\Locale()],

                'order_status' => [new Assert\NotBlank(), new Assert\Positive()],
                'date_paid' => new Assert\Optional([new Assert\NotBlank(), new Assert\DateTime()]),
                'date_shipped' => new Assert\Optional([new Assert\NotBlank(), new Assert\DateTime()]),

                'settings' => new Assert\Optional([new Assert\Type('array')])
            ],
            allowExtraFields: true
        ));

        $event->addConstraint(new NotHasKey('notes'));
    }
}
