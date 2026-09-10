<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Store\Document\NumberFormat;

/**
 * @extends TransactionEvent<string>
 */
class DocumentNumberTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.document_number';

    public const string TYPE_ORDER = 'order';

    public const string TYPE_DOCUMENT = 'document';

    public function __construct(
        private readonly NumberFormat $numberFormat,
        private readonly string $type,
        private readonly int $storeId = 0,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getNumberFormat(): NumberFormat
    {
        return $this->numberFormat;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }
}
