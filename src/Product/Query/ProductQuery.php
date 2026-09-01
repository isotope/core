<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;

class ProductQuery implements \Stringable
{
    /**
     * @var array<string, array<FilterInterface>>
     */
    private array $filters = [];

    /**
     * @var array<string, array>
     */
    private array $sortings = [];

    /**
     * @var array<string, int>
     */
    private array $limits = [];

    /**
     * @var array<string, string>
     */
    private array $search = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function __toString(): string
    {
        $query = [];

        foreach ($this->filters as $key => $filters) {
            $query['_f'.$key] = implode(';', array_map(strval(...), $filters));
        }

        foreach ($this->sortings as $key => $sorting) {
            $query['_s'.$key] = implode(';', array_map(strval(...), $sorting));
        }

        foreach ($this->limits as $key => $limit) {
            $query['_l'.$key] = $limit;
        }

        foreach ($this->search as $key => $search) {
            $query['_q'.$key] = $search;
        }

        return http_build_query($query);
    }

    public function applyFilterAndSorting(QueryBuilder $queryBuilder, array $keys): void
    {
        foreach ($this->getFilters($keys) as $filter) {
            $filter->apply($queryBuilder);
        }

        foreach ($this->getSortings($keys) as $sorting) {
            $sorting->apply($queryBuilder);
        }
    }

    public function applySearch(QueryBuilder $queryBuilder, array $fields, array $keys): void
    {
        $search = implode(' ', array_intersect_key($this->search, array_flip($keys)));
        $keywords = array_unique(explode(' ', $search));

        if ([] === $keywords) {
            return;
        }

        foreach ($keywords as $keyword) {
            $expr = [];
            $param = $queryBuilder->createNamedParameter('%'.$keyword.'%');

            foreach ($fields as $field) {
                $field = $this->connection->quoteSingleIdentifier($field);

                $expr[] = $queryBuilder->expr()->like(
                    \sprintf('IFNULL(translation.%s, tl_iso_product.%s)', $field, $field),
                    $param,
                );
            }

            $queryBuilder->andWhere($queryBuilder->expr()->or(...$expr));
        }
    }

    public function setFilters(string $key, array $filters): self
    {
        $this->filters[$key] = $filters;

        return $this;
    }

    /**
     * @param list<string> $keys
     *
     * @return array<FilterInterface>
     */
    public function getFilters(array $keys): array
    {
        return array_merge(...array_values(array_intersect_key($this->filters, array_flip($keys))));
    }

    public function setSorting(string $key, array $sorting): self
    {
        $this->sortings[$key] = $sorting;

        return $this;
    }

    /**
     * @param list<string> $keys
     *
     * @return array<Sorting>
     */
    public function getSortings(array $keys): array
    {
        return array_merge(...array_values(array_intersect_key($this->sortings, array_flip($keys))));
    }

    public function setLimit(string $key, int $limit): self
    {
        $this->limits[$key] = $limit;

        return $this;
    }

    /**
     * @param list<string> $keys
     */
    public function getLimit(array $keys): int|null
    {
        foreach ($keys as $key) {
            if (isset($this->limits[$key])) {
                return $this->limits[$key];
            }
        }

        return null;
    }

    public function setSearch(string $key, string $search): self
    {
        $this->search[$key] = $search;

        return $this;
    }

    public function getSearches(array $keys): array
    {
        return array_intersect_key($this->search, array_flip($keys));
    }
}
