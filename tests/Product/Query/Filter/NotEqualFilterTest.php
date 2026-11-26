<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Query\Filter;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\ParameterType;
use Isotope\CoreBundle\Product\Query\Filter\NotEqualFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotEqualFilterTest extends TestCase
{
    #[DataProvider('queryProvider')]
    public function testDatabaseAndInMemoryMatchingAgree(string $query, array $expectedIds): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_product (id INTEGER PRIMARY KEY, test_value TEXT NULL)');

        $values = [1 => null, 2 => '', 3 => 'red', 4 => 'blue', 5 => '0'];

        try {
            foreach ($values as $id => $value) {
                $connection->insert(
                    'tl_iso_product',
                    ['id' => $id, 'test_value' => $value],
                    [
                        'id' => ParameterType::INTEGER,
                        'test_value' => null === $value ? ParameterType::NULL : ParameterType::STRING,
                    ],
                );
            }

            $filter = new NotEqualFilter('test_value', $query);
            $qb = $connection->createQueryBuilder()->select('id')->from('tl_iso_product')->orderBy('id');
            $filter->apply($qb);

            $this->assertSame($expectedIds, array_map(intval(...), $qb->fetchFirstColumn()));

            foreach ($values as $id => $value) {
                $this->assertSame(\in_array($id, $expectedIds, true), $filter->matches([$value]));
            }
        } finally {
            $connection->close();
        }
    }

    public static function queryProvider(): iterable
    {
        yield 'exclude a value' => ['red', [1, 2, 4, 5]];
        yield 'exclude empty values' => ['', [3, 4, 5]];
        yield 'exclude zero' => ['0', [1, 2, 3, 4]];
        yield 'AND comparisons' => ['red,blue', [1, 2, 5]];
        yield 'OR comparisons' => ['red|blue', [1, 2, 3, 4, 5]];
    }
}
