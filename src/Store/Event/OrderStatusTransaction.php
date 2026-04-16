<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Model\ProductCollectionModel;

class OrderStatusTransaction extends Transaction
{
    public const string NAME = 'isotope.order_status';

    public function __construct(
        public readonly ProductCollectionModel $order,
        public readonly array $values,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }
}
