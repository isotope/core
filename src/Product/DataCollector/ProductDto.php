<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\DataCollector;

class ProductDto extends \ArrayObject
{
    private string|null $type = null;

    public function __construct(
        private readonly string $identifier,
        private readonly int $quantity = 1,
        array $data = [],
    ) {
        parent::__construct($data, \ArrayObject::STD_PROP_LIST);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getType(): string|null
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
