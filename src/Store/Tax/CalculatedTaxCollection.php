<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Ramsey\Collection\Exception\InvalidArgumentException;
use Ramsey\Collection\Map\AbstractMap;
use Ramsey\Collection\Tool\ValueToStringTrait;

/**
 * @extends AbstractMap<CalculatedTax>
 */
class CalculatedTaxCollection extends AbstractMap
{
    use ValueToStringTrait;

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return CalculatedTax::class;
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof CalculatedTax) {
            throw new InvalidArgumentException('Value must be of type '.$this->getValueType().'; value is '.$this->toolValueToString($value));
        }

        parent::offsetSet($value->getId(), $value);
    }

    public function sum(): float
    {
        return array_reduce($this->data, static fn (float $total, CalculatedTax $tax) => $total + $tax->getAmount(), 0);
    }

    public function getNumbers(): array
    {
        $numbers = [];

        /** @var CalculatedTax $tax */
        foreach ($this as $tax) {
            $numbers[] = $tax->getNumber();
        }

        return array_unique($numbers);
    }
}
