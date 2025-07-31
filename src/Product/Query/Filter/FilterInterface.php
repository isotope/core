<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query\Filter;

use Doctrine\DBAL\Query\QueryBuilder;

interface FilterInterface extends \Stringable
{
    public function __construct(string|null $attribute, string $value);

    public function getAttributeName(): string|null;

    /**
     * Apply the filter to the database query.
     */
    public function apply(QueryBuilder $qb): void;

    /**
     * Match the filter to product values, if the attribute is dynamic.
     */
    public function matches(array $productValue): bool;
}
