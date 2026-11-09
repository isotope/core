<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query\Filter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Product\Exception\InvalidQueryAttributeException;

abstract class AbstractFilter implements FilterInterface
{
    protected readonly array $queryValues;

    protected readonly bool $or;

    public function __construct(
        protected readonly string|null $attributeName,
        string $value,
    ) {
        if (null === $this->attributeName) {
            throw new InvalidQueryAttributeException('NULL');
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
        $operator = $this->getOperator();

        if (preg_match('/\w/', $operator)) {
            $operator = '('.$operator.')';
        }

        return $this->getAttributeName().$operator.implode($this->or ? '|' : ',', $this->queryValues);
    }

    public function getAttributeName(): string|null
    {
        return $this->attributeName;
    }

    /**
     * @return list<string>
     */
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
        return array_any($productValue, fn ($value) => $this->matchesOne($value));
    }

    abstract protected function getOperator(): string;

    abstract protected function applyValue(QueryBuilder $qb, mixed $queryValue): CompositeExpression|string;

    abstract protected function compare(mixed $queryValue, mixed $productValue): bool;

    protected function isMultiple(): bool
    {
        $eval = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$this->attributeName]['eval'] ?? [];

        if (!($eval['multiple'] ?? false)) {
            return false;
        }

        if (',' !== ($eval['csv'] ?? null)) {
            throw new \LogicException(\sprintf('Cannot filter attribute "%s": multiple-choice attributes must use comma-separated CSV storage.', $this->attributeName));
        }

        return true;
    }

    protected function matchesMultiple(array $productValue, bool $matchAll = false): bool
    {
        foreach ($this->queryValues as $query) {
            $result = $matchAll;

            foreach ($productValue as $value) {
                $result = $this->compare($query, $value);

                // One match is enough when matching any stored value.
                if ($result && !$matchAll) {
                    break;
                }

                // One mismatch fails when matching all stored values.
                if (!$result && $matchAll) {
                    break;
                }
            }

            if ($result && $this->or) {
                return true;
            }

            if (!$result && !$this->or) {
                return false;
            }
        }

        // In the OR case, no query condition matched, so the result is FALSE.
        // In the AND case, every query condition matched, so the result is TRUE.

        return !$this->or;
    }

    private function matchesOne(mixed $productValue): bool
    {
        foreach ($this->queryValues as $query) {
            $result = $this->compare($query, $productValue);

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
