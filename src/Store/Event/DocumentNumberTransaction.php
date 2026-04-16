<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;

class DocumentNumberTransaction extends Transaction
{
    public const string NAME = 'isotope.document_number';

    public function __construct(
        public readonly string $prefix,
        public readonly int $length,
        public readonly int $storeId,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }
}
