<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\Sort;

/**
 * @extends AbstractCollection<PriceDefinition>
 *
 * @method static where(?string $propertyOrMethod, mixed $value)
 * @method static sort(?string $propertyOrMethod = null, Sort $order = Sort::Ascending)
 * @method static merge(PriceDefinitionCollection ...$collections)
 * @method static filter(callable $callback)
 */
class PriceDefinitionCollection extends AbstractCollection
{
    public function getType(): string
    {
        return PriceDefinition::class;
    }

    public function hasRange(): bool
    {
        return \count(array_unique($this->column('getAmount'))) > 1;
    }

    public function getLowest(): PriceDefinition|null
    {
        $collection = $this->sort('getAmount');

        if ($collection->isEmpty()) {
            return null;
        }

        return $collection->first();
    }
}
