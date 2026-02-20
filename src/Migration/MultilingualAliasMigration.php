<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class MultilingualAliasMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tableExists('tl_iso_product')) {
            return false;
        }

        return $this->connection->fetchOne(<<<SQL
            SELECT COUNT(*)
            FROM tl_iso_product p
            JOIN tl_iso_product t ON p.id=t.pid AND t.language!=''
            WHERE p.pid=0 AND p.language='' AND p.alias!='' AND t.alias=''
        SQL) > 0;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            UPDATE tl_iso_product
            SET alias=(SELECT alias FROM (SELECT * FROM tl_iso_product) p WHERE p.id=tl_iso_product.pid)
            WHERE pid > 0 AND language!='' AND alias=''
        SQL);

        return $this->createResult(true);
    }
}
