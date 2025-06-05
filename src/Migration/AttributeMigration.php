<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class AttributeMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_attribute'])) {
            return false;
        }

        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_iso_attribute WHERE type='mediaManager'") > 0;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_iso_attribute SET type='media_manager' WHERE type='mediaManager'");

        return $this->createResult(true);
    }
}
