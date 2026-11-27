<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\EventListener;

use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Store\Order\Validation\LineItemValidator;
use Isotope\CoreBundle\Store\Order\Validation\OrderValidator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @internal
 */
#[AsEventListener('isotope.validation.order.create')]
class OrderValidationConstraintsListener
{
    public function __construct(
        private readonly OrderValidator $orderValidator,
        private readonly LineItemValidator $lineItemValidator,
        private readonly AddressValidator $addressValidator,
    ) {
    }

    public function __invoke(OrderValidationEvent $event): void
    {
        $context = $event->getStoreContext();

        $constraints = [
            ...$this->orderValidator->getConstraints($context),
            ...$this->lineItemValidator->getOrderConstraints($context),
            ...$this->addressValidator->getOrderConstraints($context),
        ];

        foreach ($constraints as $constraint) {
            $event->addConstraint($constraint);
        }
    }
}
