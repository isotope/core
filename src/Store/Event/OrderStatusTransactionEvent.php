<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Model\ProductCollectionModel;

class OrderStatusTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.order_status';

    public function __construct(
        private readonly ProductCollectionModel $order,
        private readonly array $values,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getOrder(): ProductCollectionModel
    {
        return $this->order;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
