<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Price;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;

/**
 * @extends AbstractCollection<Price>
 */
class PriceCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Price::class;
    }

    /**
     * @return PriceCollection
     */
    public function filterForContext(StoreContext $context): CollectionInterface
    {
        return $this->filter(static fn (Price $price) => $price->isApplicable($context));
    }

    /**
     * @return PriceCollection
     */
    public function filterDefault(): CollectionInterface
    {
        return $this->filter(static fn (Price $price) => !$price->getConfigId() && !$price->getGroupId() && null === $price->getStart() && null === $price->getStop());
    }

    public function getDefault(): Price|null
    {
        foreach ($this as $price) {
            if (!$price->getConfigId() && !$price->getGroupId()) {
                return $price;
            }
        }

        return null;
    }

    public function hasRange(): bool
    {
        return \count(array_unique($this->column('getPrice'))) > 1;
    }

    public function getLowest(): Price|null
    {
        $collection = $this->sort('getPrice');

        if ($collection->isEmpty()) {
            return null;
        }

        return $collection->first();
    }
}
