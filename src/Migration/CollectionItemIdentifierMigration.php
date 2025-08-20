<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class CollectionItemIdentifierMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_product_collection_item'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_iso_product_collection_item');

        return !\array_key_exists('identifier', $columns)
            && \array_key_exists('type', $columns)
            && \array_key_exists('product_id', $columns);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement('ALTER TABLE tl_iso_product_collection_item ADD COLUMN `identifier` varchar(64) NOT NULL');
        $this->connection->executeStatement("UPDATE tl_iso_product_collection_item SET identifier=CONCAT(type, '.', product_id)");

        return $this->createResult(true);
    }
}
