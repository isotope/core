<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Util;

use Contao\CoreBundle\Routing\PageFinder;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProductCategoryUtilTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE tl_page (id INTEGER PRIMARY KEY, type TEXT, published INTEGER, start TEXT, stop TEXT)');
        $this->connection->executeStatement('CREATE TABLE tl_iso_product_category (id INTEGER PRIMARY KEY, pid INTEGER, product_id INTEGER, product_sorting INTEGER)');
    }

    protected function tearDown(): void
    {
        $this->connection->close();
    }

    public function testPreloadPreservesLongCategoryListsAndTheirOrder(): void
    {
        foreach (array_reverse(range(0, 399)) as $offset) {
            $this->connection->insert('tl_page', ['id' => 10000 + $offset, 'type' => 'regular', 'published' => 1, 'start' => '', 'stop' => '']);
            $this->connection->insert('tl_iso_product_category', [
                'id' => $offset + 1,
                'pid' => 10000 + $offset,
                'product_id' => 1,
                'product_sorting' => intdiv($offset, 2),
            ]);
        }

        $this->connection->insert('tl_iso_product_category', ['id' => 401, 'pid' => 10000, 'product_id' => 2, 'product_sorting' => 0]);

        self::assertSame([1 => range(10000, 10399), 2 => [10000]], $this->createUtil()->preload(true));
    }

    public function testPreloadHonoursPublicationAndPageType(): void
    {
        foreach ([
            [10, 'regular', 1, '', ''],
            [11, 'regular', 0, '', ''],
            [12, 'regular', 1, (string) (time() + 3600), ''],
            [13, 'regular', 1, '', (string) (time() - 3600)],
            [14, 'error_404', 1, '', ''],
        ] as [$id, $type, $published, $start, $stop]) {
            $this->connection->insert('tl_page', compact('id', 'type', 'published', 'start', 'stop'));
            $this->connection->insert('tl_iso_product_category', ['id' => $id, 'pid' => $id, 'product_id' => 1, 'product_sorting' => $id]);
        }

        $util = $this->createUtil();

        self::assertSame([1 => [10]], $util->preload(true));
        self::assertSame([1 => [10, 11, 12, 13]], $util->preload(false));
        self::assertSame([1 => [10, 11, 12, 13]], $this->createUtil(true)->preload(true));
    }

    private function createUtil(bool $preview = false): ProductCategoryUtil
    {
        $tokenChecker = $this->createStub(TokenChecker::class);
        $tokenChecker->method('isPreviewMode')->willReturn($preview);

        return new ProductCategoryUtil(
            $this->connection,
            $tokenChecker,
            new RequestStack(),
            $this->createStub(PageFinder::class),
        );
    }
}
