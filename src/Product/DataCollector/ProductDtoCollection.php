<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\DataCollector;

use Ramsey\Collection\Exception\InvalidArgumentException;
use Ramsey\Collection\Map\AbstractMap;
use Ramsey\Collection\Tool\ValueToStringTrait;

/**
 * @extends AbstractMap<string|int, ProductDto>
 */
class ProductDtoCollection extends AbstractMap
{
    use ValueToStringTrait;

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return ProductDto::class;
    }

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof ProductDto) {
            throw new InvalidArgumentException('Value must be of type '.$this->getValueType().'; value is '.$this->toolValueToString($value));
        }

        parent::offsetSet($value->getIdentifier(), $value);
    }
}
