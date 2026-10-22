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
            && !$table->hasColumn('label')
            && !$table->hasColumn('referencePage')
            && !$table->hasColumn('unit_label')
            && $table->hasColumn('type')
            && $table->hasColumn('product_id')
            && $table->hasColumn('name')
            && $table->hasColumn('jumpTo');
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            <<<'SQL'
                ALTER TABLE tl_iso_product_collection_item
                    ADD COLUMN `identifier` varchar(64) NOT NULL,
                    ADD COLUMN `level` tinyint unsigned NOT NULL default 0,
                    ADD COLUMN `unit_label` varchar(128) NULL,
                    CHANGE COLUMN `name` `label` varchar(255) NOT NULL default '',
                    CHANGE COLUMN `jumpTo` `referencePage` int(0) unsigned NOT NULL default 0
                SQL,
        );

        if (!$this->connection->createSchemaManager()->introspectTableByUnquotedName('tl_iso_product_collection_item')->hasColumn('sorting')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product_collection_item ADD COLUMN `sorting` int(10) unsigned NOT NULL default 0');
        }

        $this->connection->executeStatement("UPDATE tl_iso_product_collection_item SET type='product', identifier=IF(product_id REGEXP '^[0-9]+$', CONCAT('tl_iso_product.', product_id), product_id)");

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

            if (is_numeric($surcharge['price'])) {
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
