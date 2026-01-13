<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class ProductPageMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (
            !$schemaManager->tablesExist(['tl_page', 'tl_iso_product_category'])
            || !$schemaManager->introspectTableByUnquotedName('tl_iso_product_category')->hasColumn('product_id')
        ) {
            return false;
        }

        // TODO: this could re-appear if a user changes all page types
        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_page WHERE type='regular' AND id IN (SELECT DISTINCT pid FROM tl_iso_product_category)") > 0;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_page SET type='iso_products' WHERE type='regular' AND id IN (SELECT DISTINCT pid FROM tl_iso_product_category)");

        return $this->createResult(true);
    }
}
