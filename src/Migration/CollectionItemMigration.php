<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class CollectionItemMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_product_collection_surcharge', 'tl_iso_product_collection_item'])) {
            return false;
        }

        $table = $schemaManager->introspectTableByUnquotedName('tl_iso_product_collection_item');

        return !$table->hasColumn('identifier')
            && !$table->hasColumn('level')
            && !$table->hasColumn('unit_label')
            && $table->hasColumn('type')
            && $table->hasColumn('product_id');
    }

    public function run(): MigrationResult
    {
        // TODO: type is no longer the prefix to product collection item

        $this->connection->executeStatement(<<<'SQL'
            ALTER TABLE tl_iso_product_collection_item
                ADD COLUMN `identifier` varchar(64) NOT NULL,
                ADD COLUMN `level` tinyint unsigned NOT NULL default 0,
                ADD COLUMN `unit_label` varchar(128) NULL,
                CHANGE COLUMN `name` `label` varchar(255) NOT NULL default ''
        SQL);

        $this->connection->executeStatement("UPDATE tl_iso_product_collection_item SET type='product', identifier=CONCAT('tl_iso_product.', product_id)");

        $surcharges = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_product_collection_surcharge ORDER BY pid, sorting');

        foreach ($surcharges as $surcharge) {

            $data = [
                'pid' => $surcharge['pid'],
                'sorting' => $surcharge['sorting'],
                'type' => $surcharge['type'],
                'identifier' => '',
                'label' => $surcharge['label'],
            ];

            if ($surcharge['source_id']) {
                $data['identifier'] = match ($surcharge['type']) {
                    'shipping' => 'tl_iso_shipping.'.$surcharge['source_id'],
                    'payment' => 'tl_iso_payment.'.$surcharge['source_id'],
                    default => '',
                };
            }

            if (\is_numeric($surcharge['price'])) {
                $data['price'] = $surcharge['price'];
            } else {
                $data['unit_label'] = str_replace('&nbsp;', '', $surcharge['price']);
            }

            $this->connection->insert('tl_iso_product_collection_item', $data);
        }

        $this->connection->executeStatement('DROP TABLE tl_iso_product_collection_surcharge');

        return $this->createResult(true);
    }
}
