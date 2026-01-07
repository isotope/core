<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Price;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Price>
 */
class PriceCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Price::class;
    }

    public function hasRange(): bool
    {
        return \count(array_unique($this->column('getAmount'))) > 1;
    }

    public function getLowest(): Price|null
    {
        $collection = $this->sort('getAmount');

        if ($collection->isEmpty()) {
            return null;
        }

        return $collection->first();
    }
}
