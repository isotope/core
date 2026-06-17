<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class NotificationMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tableExists('tl_nc_notification')) {
            return false;
        }

        return false !== $this->connection->fetchOne("SELECT TRUE FROM tl_nc_notification WHERE type='iso_order_status_change'");
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_nc_notification SET type='iso_order' WHERE type='iso_order_status_change'");

        return $this->createResult(true);
    }
}
