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
        return array_reduce($this->data, static fn (float $total, CalculatedTax $tax) => $total + $tax->getTax(), 0);
    }

    public function merge(self $taxCollection): self
    {
        $new = clone $this;

        /** @var CalculatedTax $calculatedTax */
        foreach ($taxCollection as $calculatedTax) {
            /** @var CalculatedTax|null $exists */
            $exists = $new->get($calculatedTax->getId());
            if (!$exists) {
                $new->put($calculatedTax->getId(), clone $calculatedTax);

                continue;
            }

            $exists->increment($calculatedTax);
        }

        return $new;
    }
}
