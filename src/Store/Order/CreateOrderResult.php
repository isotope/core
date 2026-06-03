<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

final class CreateOrderResult
{
    public function __construct(
        private readonly int $collectionId,
        private readonly string $identifier,
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
