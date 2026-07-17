<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @phpstan-type HashData scalar|array<scalar|array>
 */
class HashCollectionEvent extends Event
{
    /**
     * @param array<HashData> $data
     */
    public function __construct(
        private readonly AbstractProductCollection $collection,
        private array $data,
    ) {
    }

    public function getCollection(): AbstractProductCollection
    {
        return $this->collection;
    }

    /**
     * @return array<HashData>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param HashData $value
     */
    public function add(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }
}
