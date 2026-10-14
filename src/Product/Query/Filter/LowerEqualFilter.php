<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query\Filter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFilter;

#[AsProductFilter('≤')]
class LowerEqualFilter extends AbstractFilter
{
    protected function getOperator(): string
    {
        return '≤';
    }

    protected function applyValue(QueryBuilder $qb, mixed $queryValue): CompositeExpression|string
    {
        return $qb->expr()->lte('tl_iso_product.'.$this->attributeName, $qb->createNamedParameter($queryValue));
    }

    protected function compare(mixed $queryValue, mixed $productValue): bool
    {
        return $productValue <= $queryValue;
    }
}
