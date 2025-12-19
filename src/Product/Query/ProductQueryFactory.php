<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Query;

use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Exception\InvalidQueryAttributeException;
use Isotope\CoreBundle\Exception\InvalidQueryOperatorException;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductQueryFactory
{
    private const FILTER_REGEXP = '/^(\w*)(\([^)]+\)|\W)(.+)$/';

    private const SORTING_REGEXP = '/(\w+)(!?)/';

    /**
     * @param array<class-string<FilterInterface>> $filters,
     */
    public function __construct(
        private readonly array $filters,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Connection $connection,
    ) {
    }

    public function create(): ProductQuery
    {
        return new ProductQuery($this->connection);
    }

    public function createFromRequest(Request $request): ProductQuery|null
    {
        $instance = new ProductQuery($this->connection);
        $validAttributes = null;
        $isActive = false;

        foreach ($request->query->all() as $k => $v) {
            if (!\is_string($v) || !preg_match('/^_(([fsl])(\w+)|q)$/', $k, $match)) {
                continue;
            }

            $id = $match[2] ?? null;

            switch ($match[1]) {
                case 'f':
                    $instance->setFilters($id, $this->parseFilters($v, $validAttributes ??= $this->attributeRegistry->getNames()));
                    $isActive = true;
                    break;

                case 's':
                    $instance->setSorting($id, $this->parseSorting($v, $validAttributes ??= $this->attributeRegistry->getNames()));
                    $isActive = true;
                    break;

                case 'l':
                    // TODO
                    $isActive = true;
                    break;

                case 'q':
                    $instance->setSearch($v);
                    $isActive = true;
                    break;
            }
        }

        if (!$isActive) {
            return null;
        }

        return $instance;
    }

    /**
     * @return array<FilterInterface>
     */
    public function parseFilters(string $value, array $validAttributes): array
    {
        $filters = [];

        foreach (explode(';', $value) as $config) {
            if (!preg_match(self::FILTER_REGEXP, $config, $match)) {
                continue;
            }

            [, $attribute, $operator, $value] = $match;

            if ($attribute && !\in_array($attribute, $validAttributes, true)) {
                throw new InvalidQueryAttributeException($attribute);
            }

            $filters[] = $this->createFilter($attribute ?: null, $operator, $value);
        }

        return $filters;
    }

    private function createFilter(string|null $attribute, string $operator, string $value): FilterInterface
    {
        if (str_starts_with($operator, '(') && str_ends_with($operator, ')')) {
            $operator = substr($operator, 1, -1);
        }

        if (!isset($this->filters[$operator])) {
            throw new InvalidQueryOperatorException($operator);
        }

        $class = $this->filters[$operator];

        return new $class($attribute, $value);
    }

    /**
     * @return array<Sorting>
     */
    private function parseSorting(string $value, array $validAttributes): array
    {
        $sorting = [];

        foreach (explode(';', $value) as $config) {
            if (!preg_match(self::SORTING_REGEXP, $config, $match)) {
                continue;
            }

            [, $attribute, $desc] = $match;

            if (!\in_array($attribute, $validAttributes, true)) {
                throw new InvalidQueryAttributeException($attribute);
            }

            $sorting[] = new Sorting($attribute, (bool) $desc);
        }

        return $sorting;
    }
}
