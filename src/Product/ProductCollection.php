<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\Sort;

/**
 * @extends AbstractCollection<Product>
 *
 * @method static where(?string $propertyOrMethod, mixed $value)
 * @method static sort(?string $propertyOrMethod = null, Sort $order = Sort::Ascending)
 * @method static merge(ProductCollection ...$collections)
 * @method static filter(callable $callback)
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
}
