<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Pagination\Pagination;
use Contao\CoreBundle\Pagination\PaginationConfig;
use Contao\CoreBundle\Pagination\PaginationFactoryInterface;
use Contao\CoreBundle\Pagination\PaginationInterface;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Controller\ContentElement\ProductListController;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Product\Query\Sorting;
use Isotope\CoreBundle\Store\StoreManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ProductListControllerTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TEMPORARY TABLE tl_iso_product (id INTEGER PRIMARY KEY, pid INTEGER, language VARCHAR(5), name VARCHAR(255), published INTEGER, start VARCHAR(10), stop VARCHAR(10))');
        $this->connection->executeStatement('CREATE TEMPORARY TABLE tl_iso_product_category (pid INTEGER, product_id INTEGER)');

        foreach ([
            [1, 0, '', 'Apple'],
            [2, 1, '', 'Variant A'],
            [3, 1, '', 'Variant B'],
            [4, 0, '', 'Zebra'],
            [5, 0, '', 'Zulu'],
            [6, 1, 'fr', 'Pomme'],
            [7, 2, 'fr', 'Variante A'],
            [8, 4, 'fr', 'Abricot'],
        ] as [$id, $pid, $language, $name]) {
            $this->connection->insert('tl_iso_product', [
                'id' => $id,
                'pid' => $pid,
                'language' => $language,
                'name' => $name,
                'published' => 1,
                'start' => '',
                'stop' => '',
            ]);
        }

        foreach ([1, 4, 5] as $id) {
            foreach ([10, 11] as $category) {
                $this->connection->insert('tl_iso_product_category', ['pid' => $category, 'product_id' => $id]);
            }
        }
    }

    protected function tearDown(): void
    {
        $this->connection->close();
    }

    #[DataProvider('paginationProvider')]
    public function testPaginationReturnsEachProductOnce(bool $desc, int $offset, array $expected, int $numberOfItems = 0, int $perPage = 2): void
    {
        $factory = $this->createMock(PaginationFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(
                function (PaginationConfig $config) use ($offset, $numberOfItems): PaginationInterface {
                    $this->assertSame($numberOfItems > 0 ? min(3, $numberOfItems) : 3, $config->getTotal());

                    return new Pagination(null === $config->getCurrentPage() ? $config->withCurrentPage(1 + intdiv($offset, $config->getPerPage())) : $config);
                },
            )
        ;

        $controller = $this->createController($factory);
        $finder = (new ProductFinderFactory($this->connection, $this->createStub(TokenChecker::class)))->create([10, 11], 'fr')->withSorting([new Sorting('name', $desc)]);

        $model = $this->createStub(ContentModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['id', 42],
                ['numberOfItems', $numberOfItems],
                ['perPage', $perPage],
            ])
        ;

        $pagination = (new \ReflectionMethod($controller, 'getPagination'))->invoke($controller, $model, new Request(), $finder, null);

        [$pageOffset, $end] = $pagination->getIndexRange();
        $this->assertSame($expected, array_map(intval(...), $finder->findIds($end - $pageOffset, $pageOffset)));
    }

    public static function paginationProvider(): iterable
    {
        yield 'ascending first page' => [false, 0, [4, 1]];
        yield 'ascending last page' => [false, 2, [5]];
        yield 'descending first page' => [true, 0, [5, 1]];
        yield 'descending last page' => [true, 2, [4]];
        yield 'overall limit on last page' => [false, 2, [5], 3];
        yield 'overall limit without pagination' => [false, 0, [4], 1];
        yield 'pagination disabled' => [false, 0, [4, 1, 5], 0, 0];
        yield 'overall limit with pagination disabled' => [false, 0, [4, 1], 2, 0];
    }

    public function testMatchingVariantReturnsItsParentProduct(): void
    {
        $finder = (new ProductFinderFactory($this->connection, $this->createStub(TokenChecker::class)))->create([10, 11], 'fr')
            ->withFilters([new EqualFilter('name', 'Variant B')])
            ->withSorting([new Sorting('name')])
        ;

        $this->assertSame([1], array_map(intval(...), $finder->findIds()));
    }

    private function createController(PaginationFactoryInterface $factory): ProductListController
    {
        return new ProductListController(
            $this->createStub(AttributeRegistry::class),
            new ProductFinderFactory($this->connection, $this->createStub(TokenChecker::class)),
            $this->createStub(ProductQueryFactory::class),
            $this->createStub(ProductBuilder::class),
            $this->createStub(StoreManager::class),
            $factory,
        );
    }
}
