<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

final readonly class CreateOrderResult
{
    public function __construct(
        private int $collectionId,
        private string $identifier,
    ) {
    }

    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
