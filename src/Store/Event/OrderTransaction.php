<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Store\Context\StoreContext;

class OrderTransaction extends Transaction
{
    public const string NAME = 'isotope.order';

    public function __construct(
        public readonly array $data,
        public readonly StoreContext $context,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }
}
