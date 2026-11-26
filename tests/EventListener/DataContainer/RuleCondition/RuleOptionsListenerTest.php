<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\EventListener\DataContainer\RuleCondition;

use Contao\DataContainer;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\EventListener\DataContainer\RuleCondition\RuleOptionsListener;
use Isotope\CoreBundle\Test\TestCase;

final class RuleOptionsListenerTest extends TestCase
{
    public function testExcludesTheParentRuleInsteadOfTheConditionId(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_rule (id INTEGER PRIMARY KEY, name TEXT)');
        $connection->insert('tl_iso_rule', ['id' => 1, 'name' => 'Parent rule']);
        $connection->insert('tl_iso_rule', ['id' => 2, 'name' => 'Other rule']);
        $connection->insert('tl_iso_rule', ['id' => 3, 'name' => 'Third rule']);

        $dc = $this->createClassWithPropertiesStub(DataContainer::class, ['id' => 2]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['id' => 2, 'pid' => 1])
        ;

        try {
            $this->assertSame([2 => 'Other rule', 3 => 'Third rule'], (new RuleOptionsListener($connection))($dc));
        } finally {
            $connection->close();
        }
    }
}
