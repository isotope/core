<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;

class ProductQuery implements \Stringable
{
    /**
     * @var array<int, array<FilterInterface>>
     */
    private array $filters = [];

    /**
     * @var array<int, array>
     */
    private array $sortings = [];

    /**
     * @var array<int, int>
     */
    private array $limits = [];

    private string|null $search = null;

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

        if ($this->search) {
            $query['_q'] = $this->search;
        }

        return http_build_query($query);
    }

    public function apply(QueryBuilder $queryBuilder, array $keys): void
    {
        foreach ($this->getFilters($keys) as $filter) {
            $filter->apply($queryBuilder);
        }

        foreach ($this->getSortings($keys) as $sorting) {
            $sorting->apply($queryBuilder);
        }

        if (null !== ($limit = $this->getLimit($keys))) {
            $queryBuilder->setMaxResults($limit);
        }
    }

    public function applySearch(QueryBuilder $queryBuilder, array $fields): void
    {
        if (!$this->search) {
            return;
        }

        $keywords = explode(' ', $this->search);

        foreach ($keywords as $keyword) {
            $expr = [];
            $param = $queryBuilder->createNamedParameter('%'.$keyword.'%');

            foreach ($fields as $field) {
                $field = $this->connection->quoteSingleIdentifier($field);

                $expr[] = $queryBuilder->expr()->like(
                    \sprintf('IFNULL(translation.%s, tl_iso_product.%s)', $field, $field),
                    $param
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

    public function setSearch(string|null $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch(): string|null
    {
        return $this->search;
    }
}
