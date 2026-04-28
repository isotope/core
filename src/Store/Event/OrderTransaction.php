<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Store\Context\StoreContext;

class OrderTransaction extends Transaction
{
    public const string NAME = 'isotope.order';

    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(
        private array $originalData,
        private readonly StoreContext $context,
    ) {
        $this->data = $originalData;
    }

    public function getEventName(): string
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
