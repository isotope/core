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

        $columns = $schemaManager->listTableColumns('tl_iso_product_category');

        return \array_key_exists('pid', $columns)
            && \array_key_exists('page_id', $columns)
            && !\array_key_exists('product_id', $columns)
            && !\array_key_exists('product_sorting', $columns);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement('ALTER TABLE tl_iso_product_category ADD COLUMN product_id INT(10) unsigned NOT NULL DEFAULT 0, ADD COLUMN product_sorting INT(10) unsigned NOT NULL DEFAULT 0');

        $this->connection->executeStatement('UPDATE tl_iso_product_category SET product_id=pid');
        $this->connection->executeStatement('UPDATE tl_iso_product_category SET pid=page_id');

        if (\array_key_exists('orderpages', $this->connection->createSchemaManager()->listTableColumns('tl_iso_product'))) {
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

        return $this->createResult(true);
    }
}
