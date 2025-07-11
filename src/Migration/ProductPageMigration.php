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

        return $schemaManager->tablesExist(['tl_page', 'tl_iso_product_category'])
            && \array_key_exists('product_id', $schemaManager->listTableColumns('tl_iso_product_category'));
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_page SET type='iso_products' WHERE type='regular' AND id IN (SELECT DISTINCT pid FROM tl_iso_product_category)");

        return $this->createResult(true);
    }
}
