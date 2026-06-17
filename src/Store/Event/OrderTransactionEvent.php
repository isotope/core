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
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param array<mixed> $originalData
     */
    public function __construct(
        private readonly array $originalData,
        private readonly StoreContext $context,
    ) {
        $this->data = $originalData;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->context;
    }
}
