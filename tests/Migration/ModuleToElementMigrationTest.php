<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Migration;

use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Migration\Event\ModuleToElementMigrationEvent;
use Isotope\CoreBundle\Migration\ModuleToElementMigration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ModuleToElementMigrationTest extends TestCase
{
    public function testListenerJsonReplacesAnExistingJsonColumn(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_module (id INTEGER PRIMARY KEY, name TEXT, type TEXT, jsonData TEXT)');
        $connection->executeStatement('CREATE TABLE tl_content (id INTEGER PRIMARY KEY AUTOINCREMENT, type TEXT, ptable TEXT, title TEXT, module INTEGER, cteAlias INTEGER, jsonData TEXT)');
        $connection->insert('tl_module', ['id' => 1, 'name' => 'Cart', 'type' => 'iso_cart', 'jsonData' => '{"extra":"stale value"}']);
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ModuleToElementMigrationEvent::class,
            static function (ModuleToElementMigrationEvent $event): void {
                $event->jsonData['extra'] = 'listener value';
            }
        );
        $migration = new ModuleToElementMigration($connection, $dispatcher);

        try {
            $this->assertTrue($migration->run()->isSuccessful());
            $data = json_decode($connection->fetchOne('SELECT jsonData FROM tl_content'), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('listener value', $data['extra']);
        } finally {
            $connection->close();
        }
    }
}
