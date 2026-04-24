<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Model\ProductCollectionModel;

class OrderStatusTransaction extends Transaction
{
    public const string NAME = 'isotope.order_status';

    public function __construct(
        private readonly ProductCollectionModel $order,
        private readonly array $values,
    ) {
    }

    public function getEventName(): string
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
