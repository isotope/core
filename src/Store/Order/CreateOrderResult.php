<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

final readonly class CreateOrderResult
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private int $collectionId,
        private string $identifier,
        array $data = [],
    ) {
        $this->data = array_replace($data, ['id' => $collectionId, 'uniqid' => $identifier]);
    }

    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
