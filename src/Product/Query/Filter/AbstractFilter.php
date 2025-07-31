<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query\Filter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Exception\InvalidQueryAttributeException;

abstract class AbstractFilter implements FilterInterface
{
    protected readonly array $queryValues;
    protected readonly bool $or;

    public function __construct(
        protected readonly string|null $attributeName,
        string $value,
    ) {
        if (null === $this->attributeName) {
            throw new InvalidQueryAttributeException('');
        }

        if (str_contains($value, '|')) {
            $this->queryValues = explode('|', $value);
            $this->or = true;
        } else {
            $this->queryValues = explode(',', $value);
            $this->or = false;
        }
    }

    public function __toString(): string
    {
        return $this->getAttributeName().$this->getOperator().implode(',', $this->queryValues);
    }

    public function getAttributeName(): string|null
    {
        return $this->attributeName;
    }

    public function getValue(): array
    {
        return $this->queryValues;
    }

    public function apply(QueryBuilder $qb): void
    {
        $expressions = array_map(
            fn ($value) => $this->applyValue($qb, $value),
            $this->queryValues,
        );

        if ($this->or) {
            $qb->andWhere($qb->expr()->or(...$expressions));
        } else {
            $qb->andWhere($qb->expr()->and(...$expressions));
        }
    }

    public function matches(array $productValue): bool
    {
        foreach ($productValue as $value) {
            if ($this->matchesOne($value)) {
                return true;
            }
        }

        return false;
    }

    abstract protected function getOperator(): string;

    abstract protected function applyValue(QueryBuilder $qb, mixed $queryValue): CompositeExpression|string;

    abstract protected function compare(mixed $queryValue, mixed $productValue): bool;

    private function matchesOne(mixed $productValue): bool
    {
        foreach ($this->queryValues as $query) {
            $result = $this->compare($productValue, $query);

            // One match in OR case, abort the loop
            if ($result && $this->or) {
                return true;
            }

            // One miss in AND case, abort the loop
            if (!$result && !$this->or) {
                return false;
            }
        }

        // If we land here in OR case, it means no result was ever TRUE (we didn't abort), so the result is FALSE
        // If we land here in AND case, it means all results where matches (we didn't abort), so the result is TRUE

        return !$this->or;
    }
}
