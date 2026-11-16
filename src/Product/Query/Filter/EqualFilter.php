<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query\Filter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFilter;

#[AsProductFilter(':')]
class EqualFilter extends AbstractFilter
{
    public function getOperator(): string
    {
        return ':';
    }

    #[\Override]
    public function matches(array $productValue): bool
    {
        return $this->isMultiple() ? $this->matchesMultiple($productValue) : parent::matches($productValue);
    }

    protected function applyValue(QueryBuilder $qb, mixed $queryValue): CompositeExpression|string
    {
        if ($this->isMultiple()) {
            return $qb->expr()->gt('FIND_IN_SET('.$qb->createNamedParameter($queryValue).', tl_iso_product.'.$this->attributeName.')', '0');
        }

        $expression = $qb->expr()->eq('tl_iso_product.'.$this->attributeName, $qb->createNamedParameter($queryValue));

        return '' === $queryValue ? $qb->expr()->or($expression, $qb->expr()->isNull('tl_iso_product.'.$this->attributeName)) : $expression;
    }

    protected function compare(mixed $queryValue, mixed $productValue): bool
    {
        return (string) $queryValue === (string) $productValue;
    }
}
