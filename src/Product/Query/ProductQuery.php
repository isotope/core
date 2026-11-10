<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query;

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
