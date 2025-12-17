<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Product>
 */
class ProductCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Product::class;
    }

    public function findByIdentifier(string $identifier): Product|null
    {
        foreach ($this as $product) {
            if ($product->getIdentifier() === $identifier) {
                return $product;
            }
        }

        return null;
    }

    public function findByConfiguration(array $configuration): Product|null
    {
        $collection = $this;

        foreach ($configuration as $k => $v) {
            $collection = $collection->filter(static fn (Product $product) => $product->{$k} === $v);
        }

        return 1 === $collection->count() ? $collection->first() : null;
    }
}
