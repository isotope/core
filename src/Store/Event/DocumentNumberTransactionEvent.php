<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;

/**
 * @extends TransactionEvent<string>
 */
class DocumentNumberTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.document_number';

    public function __construct(
        private readonly string $prefix,
        private readonly int $length,
        private readonly int $storeId,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }
}
