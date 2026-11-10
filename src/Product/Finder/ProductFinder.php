<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Finder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;
use Isotope\CoreBundle\Product\Query\Sorting;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class ProductFinder
{
    private readonly QueryBuilder $baseQuery;

    /**
     * @var array<FilterInterface>
     */
    private array $filters = [];

    /**
     * @var array<Sorting>
     */
    private array $sortings = [];

    /**
     * @var list<string>
     */
    private array $searchFields = [];

    /**
     * @var list<string>
     */
    private array $keywords = [];

    /**
     * @param array<FilterInterface> $baseFilters
     * @param array<Sorting>         $baseSortings
     */
    public function __construct(
        private readonly Connection $connection,
        QueryBuilder $baseQuery,
        private readonly array $baseFilters = [],
        private readonly array $baseSortings = [],
    ) {
        $this->baseQuery = clone $baseQuery;
    }

    /**
     * @param array<FilterInterface> $filters
     */
    public function withFilters(array $filters): self
    {
        $finder = clone $this;
        $finder->filters = $filters;

        return $finder;
    }

    /**
     * @param array<Sorting> $sortings
     */
    public function withSorting(array $sortings): self
    {
        $finder = clone $this;
        $finder->sortings = $sortings;

        return $finder;
    }

    /**
     * @param list<string>  $fields
     * @param array<string> $keywords
     */
    public function withSearch(array $fields, array $keywords): self
    {
        $finder = clone $this;
        $finder->searchFields = $fields;
        $finder->keywords = array_values(array_unique(array_filter(
            explode(' ', implode(' ', $keywords)),
            static fn (string $keyword): bool => '' !== $keyword,
        )));

        return $finder;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $qb = clone $this->baseQuery;

        foreach ([...$this->filters, ...$this->baseFilters] as $filter) {
            $filter->apply($qb);
        }

        // Active sorting takes precedence; base sorting resolves remaining ties.
        foreach ([...$this->sortings, ...$this->baseSortings] as $sorting) {
            $sorting->apply($qb);
        }

        if ([] !== $this->searchFields) {
            foreach ($this->keywords as $keyword) {
                $expressions = [];
                $parameter = $qb->createNamedParameter('%'.$keyword.'%');

                foreach ($this->searchFields as $field) {
                    $field = $this->connection->quoteSingleIdentifier($field);
                    $expressions[] = $qb->expr()->like(
                        \sprintf('IFNULL(translation.%s, tl_iso_product.%s)', $field, $field),
                        $parameter,
                    );
                }

                $qb->andWhere($qb->expr()->or(...$expressions));
            }
        }

        return $qb;
    }

    public function count(): int
    {
        return (int) $this->getQueryBuilder()
            ->select('COUNT(DISTINCT IFNULL(parent.id, tl_iso_product.id))')
            ->resetGroupBy()
            ->resetOrderBy()
            ->setFirstResult(0)
            ->setMaxResults(null)
            ->fetchOne()
        ;
    }

    /**
     * @return list<int|string>
     */
    public function findIds(int|null $limit = null, int $offset = 0): array
    {
        return $this->getQueryBuilder()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->fetchFirstColumn()
        ;
    }
}
