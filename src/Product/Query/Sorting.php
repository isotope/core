<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query;

use Doctrine\DBAL\Query\QueryBuilder;

class Sorting implements \Stringable
{
    public function __construct(
        public readonly string $attribute,
        public readonly bool $desc = false,
    ) {
    }

    public function __toString(): string
    {
        return $this->attribute.($this->desc ? '!' : '');
    }

    /**
     * Apply the sorting to the database query.
     */
    public function apply(QueryBuilder $qb): void
    {
        $qb->addOrderBy(
            \sprintf(
                'COALESCE(parent_translation.%1$s, parent.%1$s, translation.%1$s, tl_iso_product.%1$s)',
                $this->attribute,
            ),
            $this->desc ? 'DESC' : null,
        );
    }

    /**
     * Match the filter to product values, if the attribute is dynamic.
     */
    public function sort(mixed $a, mixed $b): int
    {
        return $a <=> $b;
    }
}
