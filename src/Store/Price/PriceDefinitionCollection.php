<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<PriceDefinition>
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
