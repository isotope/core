<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class ProductCategoryMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_product', 'tl_iso_product_category'])) {
            return false;
        }

        $table = $schemaManager->introspectTableByUnquotedName('tl_iso_product_category');

        return $table->hasColumn('pid')
            && $table->hasColumn('page_id')
            && !$table->hasColumn('product_id')
            && !$table->hasColumn('product_sorting');
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        $this->connection->executeStatement('ALTER TABLE tl_iso_product_category ADD COLUMN product_id INT(10) unsigned NOT NULL DEFAULT 0, ADD COLUMN product_sorting INT(10) unsigned NOT NULL DEFAULT 0');
        $this->connection->executeStatement('UPDATE tl_iso_product_category SET product_id=pid');
        $this->connection->executeStatement('UPDATE tl_iso_product_category SET pid=page_id');

        if ($schemaManager->introspectTableByUnquotedName('tl_iso_product')->hasColumn('orderPages')) {
            $data = $this->connection->fetchAllKeyValue('SELECT id, orderPages FROM tl_iso_product');

            foreach ($data as $productId => $order) {
                $order = StringUtil::deserialize($order);

                if (!\is_array($order)) {
                    continue;
                }

                foreach ($order as $sorting => $pid) {
                    $this->connection->executeStatement(
                        'UPDATE tl_iso_product_category SET product_sorting=? WHERE product_id=? AND pid=?',
                        [$sorting, $productId, $pid],
                    );
                }
            }
        }

        $this->connection->executeStatement('ALTER TABLE tl_iso_product_category DROP COLUMN page_id');
        $this->connection->executeStatement('ALTER TABLE tl_iso_product DROP COLUMN orderPages');

        if ($schemaManager->tablesExist(['tl_page'])) {
            $this->connection->executeStatement("UPDATE tl_page SET type='iso_products' WHERE type='regular' AND id IN (SELECT pid FROM tl_iso_product_category)");
        }

        return $this->createResult(true);
    }
}
