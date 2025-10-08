<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PriceCalculateModeMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_config'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_iso_config');

        return array_key_exists('priceCalculateFactor', $columns) && array_key_exists('priceCalculateMode', $columns);
    }

    public function run(): MigrationResult
    {
        $configs = $this->connection->fetchAllKeyValue("SELECT id, priceCalculateFactor FROM tl_iso_config WHERE priceCalculateMode='div'");

        foreach ($configs as $id => $factor) {
            $this->connection->update(
                'tl_iso_config',
                ['priceCalculateFactor' => substr((string) (1 / $factor), 16)],
                ['id' => $id],
            );
        }

        $this->connection->executeStatement('ALTER TABLE tl_iso_config DROP COLUMN priceCalculateMode');

        return $this->createResult(true);
    }
}
