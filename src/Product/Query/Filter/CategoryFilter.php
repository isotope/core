<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query\Filter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFilter;

#[AsProductFilter('cat')]
class CategoryFilter implements FilterInterface
{
    public array $values;

    public function __construct(
        protected readonly string|null $attributeName,
        string $value,
    ) {
        $this->values = array_map(intval(...), explode(',', $value));
    }

    public function __toString(): string
    {
        return $this->getAttributeName().'(cat)'.implode(',', $this->values);
    }

    public function getAttributeName(): string|null
    {
        return $this->attributeName;
    }

    public function apply(QueryBuilder $qb): void
    {
        $qb->andWhere($qb->expr()->in('c.pid', $this->values));
    }

    public function matches(array $productValue): bool
    {
        return true;
    }
}
