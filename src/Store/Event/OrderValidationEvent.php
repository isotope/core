<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\EventDispatcher\Event;

class OrderValidationEvent extends Event
{
    /**
     * @var array<Constraint>
     */
    private array $constraints = [];

    public function __construct(
        private readonly array $orderData,
        private readonly StoreContext $context
    ) {
    }

    public function getOrderData(): array
    {
        return $this->orderData;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->context;
    }

    /**
     * @return array<Constraint>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function addConstraint(Constraint $constraint): self
    {
        $this->constraints[] = $constraint;

        return $this;
    }
}
