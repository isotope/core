<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Cron;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Isotope\CoreBundle\Cron\PurgeMediaCron;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

final class PurgeMediaCronTest extends ContaoTestCase
{
    private Connection $connection;
    private Filesystem $filesystem;
    private string $projectDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE tl_iso_product (id INTEGER PRIMARY KEY, pid INTEGER, language TEXT, published INTEGER, images BLOB, downloads BLOB)');
        $this->filesystem = new Filesystem();
        $this->projectDir = sys_get_temp_dir().'/isotope-media-'.bin2hex(random_bytes(8));
        $this->filesystem->mkdir($this->projectDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDir);
        $this->connection->close();

        parent::tearDown();
    }

    public function testRemovesUnusedMediaAndPreservesAllProductReferences(): void
    {
        foreach ([
            [1, 0, '', 1, 'product.jpg', 'manual.pdf'],
            [2, 1, '', 1, 'variant.jpg', null],
            [3, 1, 'de', 1, 'translation.jpg', null],
            [4, 0, '', 0, 'unpublished.jpg', null],
            [5, 0, '', 1, 'isotope/p/path.jpg', null],
        ] as [$id, $pid, $language, $published, $image, $download]) {
            $this->connection->insert('tl_iso_product', [
                'id' => $id,
                'pid' => $pid,
                'language' => $language,
                'published' => $published,
                'images' => serialize([['src' => $image]]),
                'downloads' => null === $download ? null : serialize([['src' => $download]]),
            ]);
        }

        $retained = [
            'isotope/p/product.jpg',
            'isotope/m/manual.pdf',
            'isotope/v/variant.jpg',
            'isotope/t/translation.jpg',
            'isotope/u/unpublished.jpg',
            'isotope/p/path.jpg',
        ];

        foreach ($retained as $path) {
            $this->createFile($path);
        }

        $this->createFile('isotope/o/orphan.jpg');
        $this->createFile('isotope/o/orphan.pdf');
        $this->createCron()();

        foreach ($retained as $path) {
            self::assertFileExists($this->projectDir.'/'.$path);
        }

        self::assertFileDoesNotExist($this->projectDir.'/isotope/o/orphan.jpg');
        self::assertFileDoesNotExist($this->projectDir.'/isotope/o/orphan.pdf');
    }

    public function testPreservesRecentFilesAndFilesOutsideTheMediaManagerLayout(): void
    {
        $retained = [
            'isotope/.htaccess',
            'isotope/root.txt',
            'isotope/a/.htaccess',
            'isotope/a/nested/file.txt',
            'isotope/custom/file.txt',
        ];

        foreach ($retained as $path) {
            $this->createFile($path);
        }

        $this->createFile('isotope/r/recent.jpg', time());
        $this->createFile('isotope/o/orphan.jpg');
        $this->createCron()();

        foreach ([...$retained, 'isotope/r/recent.jpg'] as $path) {
            self::assertFileExists($this->projectDir.'/'.$path);
        }

        self::assertFileDoesNotExist($this->projectDir.'/isotope/o/orphan.jpg');
    }

    public function testDoesNotFollowSymbolicLinks(): void
    {
        $this->createFile('outside/file.jpg');
        $this->filesystem->mkdir($this->projectDir.'/isotope/a');
        $this->filesystem->symlink($this->projectDir.'/outside', $this->projectDir.'/isotope/o');
        $this->filesystem->symlink($this->projectDir.'/outside/file.jpg', $this->projectDir.'/isotope/a/alias.jpg');
        $this->createCron()();

        self::assertFileExists($this->projectDir.'/outside/file.jpg');
        self::assertTrue(is_link($this->projectDir.'/isotope/a/alias.jpg'));
        self::assertTrue(is_link($this->projectDir.'/isotope/o'));
    }

    public function testDoesNotDeleteAnythingWhenReadingReferencesFails(): void
    {
        $this->createFile('isotope/o/orphan.jpg');
        $this->connection->executeStatement('DROP TABLE tl_iso_product');

        try {
            $this->createCron()();
            self::fail('Expected the database query to fail.');
        } catch (TableNotFoundException) {
            self::assertFileExists($this->projectDir.'/isotope/o/orphan.jpg');
        }
    }

    public function testMissingDirectoryDoesNotRequireTheDatabase(): void
    {
        $this->connection->executeStatement('DROP TABLE tl_iso_product');
        $this->createCron()();

        self::assertDirectoryDoesNotExist($this->projectDir.'/isotope');
    }

    public function testRunsMonthly(): void
    {
        $attributes = (new \ReflectionClass(PurgeMediaCron::class))->getAttributes(AsCronJob::class);

        self::assertCount(1, $attributes);
        self::assertSame('monthly', $attributes[0]->newInstance()->interval);
    }

    private function createCron(): PurgeMediaCron
    {
        $controller = $this->createAdapterStub(['loadDataContainer']);
        $controller->method('loadDataContainer')->willReturnCallback(static function (string $table): void {
            self::assertSame('tl_iso_product', $table);

            $GLOBALS['TL_DCA'][$table]['fields'] = [
                'images' => ['inputType' => 'mediaManager', 'sql' => 'blob NULL'],
                'downloads' => ['inputType' => 'mediaManager', 'sql' => 'blob NULL'],
                'virtual' => ['inputType' => 'mediaManager'],
                'name' => ['inputType' => 'text', 'sql' => "varchar(255) NOT NULL default ''"],
            ];
        });

        return new PurgeMediaCron(
            $this->createContaoFrameworkStub([Controller::class => $controller]),
            $this->connection,
            $this->filesystem,
            new NullLogger(),
            $this->projectDir,
        );
    }

    private function createFile(string $path, int|null $mtime = null): void
    {
        $path = $this->projectDir.'/'.$path;
        $this->filesystem->dumpFile($path, 'media');
        $this->filesystem->touch($path, $mtime ?? time() - 172800);
    }
}
