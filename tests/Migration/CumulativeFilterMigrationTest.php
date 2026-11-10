<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Migration;

use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Migration\ModuleToElementMigration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CumulativeFilterMigrationTest extends TestCase
{
    public function testMigrationPreservesConfigurationAndReferences(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_module (id INTEGER PRIMARY KEY, pid INTEGER, name TEXT, type TEXT, iso_cumulativeFields BLOB, iso_filterModules BLOB, iso_category_scope TEXT, iso_filterHideSingle TEXT, iso_listingSortField TEXT, iso_listingSortDirection TEXT, jumpTo INTEGER, rootPage INTEGER, defineRoot TEXT, customTpl TEXT)');
        $connection->executeStatement('CREATE TABLE tl_content (id INTEGER PRIMARY KEY AUTOINCREMENT, pid INTEGER, ptable TEXT, title TEXT, type TEXT, module INTEGER, cteAlias INTEGER, jsonData TEXT)');
        $connection->executeStatement('CREATE TABLE tl_layout (id INTEGER PRIMARY KEY, modules BLOB)');
        $rows = serialize([
            ['attribute' => 'color', 'queryType' => 'or', 'matchCount' => 'new'],
            ['attribute' => 'size', 'queryType' => 'and', 'matchCount' => 'all'],
        ]);
        $configuration = [
            'id' => 42,
            'pid' => 3,
            'name' => 'Cumulative filter',
            'type' => 'iso_cumulativefilter',
            'iso_cumulativeFields' => $rows,
            'iso_category_scope' => 'current_and_all_children',
            'iso_filterHideSingle' => '1',
            'iso_listingSortField' => 'name',
            'iso_listingSortDirection' => 'ASC',
            'jumpTo' => 7,
            'rootPage' => 8,
            'defineRoot' => '1',
            'customTpl' => 'mod_iso_cumulativefilter_custom',
        ];
        $connection->insert('tl_module', $configuration);
        $connection->insert('tl_module', ['id' => 43, 'pid' => 3, 'name' => 'Product list', 'type' => 'iso_productlist', 'iso_filterModules' => serialize([42, 44])]);
        $connection->insert('tl_module', ['id' => 44, 'pid' => 3, 'name' => 'Category filter', 'type' => 'iso_categoryfilter']);
        $connection->insert('tl_content', ['id' => 1, 'type' => 'module', 'module' => 42, 'pid' => 9, 'ptable' => 'tl_article']);
        $connection->insert('tl_layout', ['id' => 1, 'modules' => serialize([['mod' => 42, 'col' => 'left', 'enable' => '1']])]);
        $migration = new ModuleToElementMigration($connection, new EventDispatcher());

        $this->assertTrue($migration->shouldRun());
        $this->assertTrue($migration->run()->isSuccessful());
        $this->assertFalse($migration->shouldRun());

        $element = $connection->fetchAssociative("SELECT * FROM tl_content WHERE type='iso_cumulativefilter'");
        $this->assertSame('tl_theme', $element['ptable']);
        $this->assertSame(3, $element['pid']);
        $this->assertSame('Cumulative filter', $element['title']);
        $data = json_decode($element['jsonData'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('', $data['iso_filterKey'] ?? '');
        $this->assertSame($rows, $data['iso_cumulativeFields']);

        foreach (['iso_category_scope', 'iso_filterHideSingle', 'iso_listingSortField', 'iso_listingSortDirection', 'jumpTo', 'rootPage', 'defineRoot'] as $field) {
            $this->assertSame($configuration[$field], $data[$field], $field.' must survive migration.');
        }

        $this->assertArrayNotHasKey('customTpl', $data);
        $alias = $connection->fetchAssociative('SELECT * FROM tl_content WHERE id=1');
        $this->assertSame('alias', $alias['type']);
        $this->assertSame($element['id'], $alias['cteAlias']);
        $layout = unserialize($connection->fetchOne('SELECT modules FROM tl_layout WHERE id=1'), ['allowed_classes' => false]);
        $this->assertSame('content-'.$element['id'], $layout[0]['mod']);
        $this->assertSame('left', $layout[0]['col']);
        $list = json_decode($connection->fetchOne("SELECT jsonData FROM tl_content WHERE type='iso_productlist'"), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('', $list['iso_filterKey'] ?? '');
        $connection->close();
    }
}
