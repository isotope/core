<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;

/**
 * @extends TransactionEvent<CreateOrderResult>
 */
class OrderTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.order';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data,
        private readonly StoreContext $context,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->context;
    }
}
