<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Finder;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Product\Query\Sorting;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProductFinderTest extends TestCase
{
    public function testQueryRoundTripAndCounts(): void
    {
        $connection = $this->createConnection();
        $attributes = $this->createStub(AttributeRegistry::class);
        $attributes
            ->method('getNames')
            ->willReturn(['color'])
        ;
        $factory = new ProductQueryFactory(
            [':' => EqualFilter::class],
            $attributes,
            new RequestStack(),
        );
        $query = $factory->create()->setFilters('shop', [new EqualFilter('color', 'red|blue')]);
        $restored = $factory->createFromRequest(Request::create('/?'.$query));
        $this->assertSame((string) $query, (string) $restored);

        $finderFactory = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $finder = $finderFactory->create([10, 11], 'en');

        // Matching variants and assignments to multiple categories count their parent only once.
        $this->assertSame(2, $finder->count());
        $this->assertInstanceOf(ProductQuery::class, $restored);
        $this->assertSame(2, $finder->withFilters($restored->getFilters(['shop']))->count());
        $this->assertSame(1, $finder->withFilters([new EqualFilter('color', 'blue')])->count());
        $this->assertSame(0, $finderFactory->create([], 'en')->count());
        $this->assertSame(1, $finderFactory->create([20], 'en')->count());
        $this->assertSame(2, $finder->count(), 'Counting must not mutate the original selection.');

        $connection->close();
    }

    public function testReplacingActiveFiltersPreservesBaseFiltersAndOtherFinders(): void
    {
        $connection = $this->createConnection();
        $factory = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $unrestricted = $factory->create([10, 11], 'en');
        $base = $factory->create([10, 11], 'en', [new EqualFilter('id', '1|2|3')]);

        try {
            $red = $base->withFilters([new EqualFilter('color', 'red')]);
            $blue = $red->withFilters([new EqualFilter('color', 'blue')]);

            $this->assertSame(1, $red->count());
            $this->assertSame(1, $blue->count(), 'Replacing active filters must not accumulate the previous selection.');
            $this->assertSame(0, $base->withFilters([new EqualFilter('id', '4')])->count(), 'Base filters always remain applied.');
            $this->assertSame(1, $blue->withFilters([])->count());
            $this->assertSame(1, $base->count());
            $this->assertSame(2, $unrestricted->count(), 'Factory instances must have independent selections.');
        } finally {
            $connection->close();
        }
    }

    public function testSortingPaginationAndReturnedQueriesDoNotMutateTheFinder(): void
    {
        $connection = $this->createConnection();
        $factory = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $ascending = $factory->create([10, 11], 'en')->withSorting([new Sorting('id')]);
        $descending = $ascending->withSorting([new Sorting('id', true)]);

        try {
            $this->assertSame([4, 1], array_map(intval(...), $descending->findIds()));
            $this->assertSame([4], array_map(intval(...), $ascending->findIds(1, 1)));
            $this->assertSame([], $ascending->findIds(0));
            $this->assertSame(2, $ascending->count(), 'Counting ignores pagination and sorting.');
            $this->assertSame([1, 4], array_map(intval(...), $ascending->findIds()));
            $ascending->getQueryBuilder()->andWhere('1=0');
            $this->assertSame(2, $ascending->count(), 'Mutating a returned query must not change the finder.');
        } finally {
            $connection->close();
        }
    }

    public function testActiveSortingTakesPrecedenceAndPreservesBaseSorting(): void
    {
        $connection = $this->createConnection();
        $factory = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $base = $factory->create([10, 11], 'en', [], [new Sorting('id', true)]);

        try {
            $this->assertSame([4, 1], array_map(intval(...), $base->findIds()));
            $this->assertSame([4, 1], array_map(intval(...), $base->withSorting([new Sorting('color')])->findIds()), 'Base sorting resolves ties in active sorting.');

            $connection->update('tl_iso_product', ['color' => 'green'], ['id' => 4]);
            $active = $base->withSorting([new Sorting('color', true)]);

            $this->assertSame([1, 4], array_map(intval(...), $active->findIds()), 'Active sorting takes precedence over base sorting.');
            $this->assertSame([4, 1], array_map(intval(...), $active->withSorting([])->findIds()), 'Clearing active sorting preserves base sorting.');
            $this->assertSame([4, 1], array_map(intval(...), $base->findIds()));
            $this->assertSame(2, $active->count());
        } finally {
            $connection->close();
        }
    }

    public function testSearchUsesTranslationsAndRequiresEveryKeywordAcrossFields(): void
    {
        $connection = $this->createConnection();
        $connection->executeStatement("ALTER TABLE tl_iso_product ADD COLUMN name TEXT DEFAULT ''");
        $connection->update('tl_iso_product', ['color' => 'red shirt'], ['id' => 1]);
        $connection->update('tl_iso_product', ['color' => 'red shoes', 'name' => 'hoodie'], ['id' => 4]);
        $connection->insert('tl_iso_product', ['id' => 10, 'pid' => 1, 'language' => 'de', 'color' => 'rotes hemd']);
        $factory = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $base = $factory->create([10, 11], 'de');

        try {
            $translated = $base->withSearch(['color'], ['rotes hemd', 'hemd']);
            $this->assertSame([1], array_map(intval(...), $translated->findIds()));
            $this->assertSame(1, $translated->count());
            $this->assertSame([4], array_map(intval(...), $base->withSearch(['color', 'name'], ['red hoodie'])->findIds()));
            $this->assertSame(0, $base->withSearch(['color'], ['rotes hoodie'])->count());
            $this->assertSame(0, $factory->create([10, 11], 'en')->withSearch(['color'], ['rotes'])->count());
            $this->assertSame(2, $translated->withSearch([], ['rotes'])->count());
            $this->assertSame(2, $translated->withSearch(['color'], [])->count());
            $this->assertSame(2, $base->count(), 'Search configuration must not modify the original finder.');
        } finally {
            $connection->close();
        }
    }

    public function testSearchPreservesZeroAndBindsKeywords(): void
    {
        $connection = $this->createConnection();
        $connection->update('tl_iso_product', ['color' => '0'], ['id' => 4]);
        $factory = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $base = $factory->create([10, 11], 'en');

        try {
            $this->assertSame([4], array_map(intval(...), $base->withSearch(['color'], [' 0  0 '])->findIds()));
            $this->assertSame(0, $base->withSearch(['color'], ["' OR 1=1 --"])->count());
            $this->assertSame(2, $base->count());
        } finally {
            $connection->close();
        }
    }

    public function testPreviewModeIncludesUnpublishedProducts(): void
    {
        $connection = $this->createConnection();
        $tokenChecker = $this->createStub(TokenChecker::class);
        $tokenChecker
            ->method('isPreviewMode')
            ->willReturn(true)
        ;
        $factory = new ProductFinderFactory($connection, $tokenChecker);

        try {
            $this->assertSame(5, $factory->create([10, 11], 'en')->count());
            $this->assertSame(0, $factory->create([], 'en')->count());
        } finally {
            $connection->close();
        }
    }

    private function createConnection(): Connection
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_product (id INTEGER PRIMARY KEY, pid INTEGER, language TEXT, published INTEGER, start TEXT, stop TEXT, color TEXT)');
        $connection->executeStatement('CREATE TABLE tl_iso_product_category (pid INTEGER, product_id INTEGER)');

        foreach ([
            [1, 0, '', 1, '', '', 'red'],
            [2, 1, '', 1, '', '', 'blue'],
            [3, 1, '', 1, '', '', 'blue'],
            [4, 0, '', 1, '', '', 'red'],
            [5, 0, '', 0, '', '', 'red'],
            [6, 5, '', 1, '', '', 'red'],
            [7, 0, '', 1, (string) (time() + 3600), '', 'red'],
            [8, 0, '', 1, '', (string) (time() - 3600), 'red'],
            [9, 0, '', 1, '', '', 'blue'],
        ] as $row) {
            $connection->insert('tl_iso_product', array_combine(['id', 'pid', 'language', 'published', 'start', 'stop', 'color'], $row));
        }

        foreach ([1, 4, 5, 7, 8] as $id) {
            foreach ([10, 11] as $category) {
                $connection->insert('tl_iso_product_category', ['pid' => $category, 'product_id' => $id]);
            }
        }

        $connection->insert('tl_iso_product_category', ['pid' => 20, 'product_id' => 9]);

        return $connection;
    }
}
