<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class ShippingCalculationMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (
            !$schemaManager->tablesExist(['tl_iso_shipping'])
            || !$schemaManager->introspectTableByUnquotedName('tl_iso_shipping')->hasColumn('product_types_condition')
        ) {
            return false;
        }

        return false !== $this->connection->fetchOne("SELECT TRUE FROM tl_iso_shipping WHERE product_types_condition='calculation'");
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->introspectTableByUnquotedName('tl_iso_shipping')->hasColumn('flatProductTypes')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_shipping ADD COLUMN `flatProductTypes` blob NULL');
        }

        $this->connection->executeStatement("
            UPDATE tl_iso_shipping
            SET flatProductTypes=product_types,
                product_types_condition='onlyAvailable'
            WHERE product_types_condition='calculation'
        ");

        return $this->createResult(true);
    }
}
