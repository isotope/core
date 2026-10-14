<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Pagination\PaginationConfig;
use Contao\CoreBundle\Pagination\PaginationFactoryInterface;
use Contao\CoreBundle\Pagination\PaginationInterface;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Controller\ContentElement\ProductListController;
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
    public function testPaginationReturnsEachProductOnce(bool $desc, int $offset, array $expected): void
    {
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPerPage')->willReturn(2);
        $pagination->method('getOffset')->willReturn($offset);

        $factory = $this->createMock(PaginationFactoryInterface::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($this->callback(static fn (PaginationConfig $config): bool => 3 === $config->getTotal()))
            ->willReturn($pagination)
        ;

        $controller = $this->createController($factory);
        $qb = $this->prepareQuery($controller);
        (new Sorting('name', $desc))->apply($qb);

        $model = $this->createStub(ContentModel::class);
        $model->method('__get')->willReturnMap([
            ['id', 42],
            ['numberOfItems', 0],
            ['perPage', 2],
        ]);

        $result = (new \ReflectionMethod($controller, 'applyPagination'))->invoke($controller, $model, new Request(), $qb, null);

        self::assertSame($pagination, $result);
        self::assertSame($expected, array_map(intval(...), $qb->fetchFirstColumn()));
    }

    public static function paginationProvider(): iterable
    {
        yield 'ascending first page' => [false, 0, [4, 1]];
        yield 'ascending last page' => [false, 2, [5]];
        yield 'descending first page' => [true, 0, [5, 1]];
        yield 'descending last page' => [true, 2, [4]];
    }

    public function testMatchingVariantReturnsItsParentProduct(): void
    {
        $controller = $this->createController($this->createStub(PaginationFactoryInterface::class));
        $qb = $this->prepareQuery($controller);
        (new EqualFilter('name', 'Variant B'))->apply($qb);
        (new Sorting('name'))->apply($qb);

        self::assertSame([1], array_map(intval(...), $qb->fetchFirstColumn()));
    }

    private function createController(PaginationFactoryInterface $factory): ProductListController
    {
        return new ProductListController(
            $this->connection,
            $this->createStub(AttributeRegistry::class),
            $this->createStub(ProductQueryFactory::class),
            $this->createStub(TokenChecker::class),
            $this->createStub(ProductBuilder::class),
            $this->createStub(StoreManager::class),
            $factory,
        );
    }

    private function prepareQuery(ProductListController $controller): QueryBuilder
    {
        return (new \ReflectionMethod($controller, 'prepareQueryBuilder'))->invoke($controller, [10, 11], 'fr');
    }
}
