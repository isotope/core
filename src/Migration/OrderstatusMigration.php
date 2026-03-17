<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\StringType;

class OrderstatusMigration extends AbstractMigration
{
    private const array DEFAULTS = [
        'order' => [
            ['alias' => 'pending', 'name' => 'Pending', 'workflow' => 'processing,complete,on_hold,cancelled', 'welcomescreen' => '1'],
            ['alias' => 'processing', 'name' => 'Processing', 'workflow' => 'complete,cancelled'],
            ['alias' => 'complete', 'name' => 'Complete', 'workflow' => 'cancelled,pending'],
            ['alias' => 'on_hold', 'name' => 'On Hold', 'workflow' => 'processing,complete,cancelled'],
            ['alias' => 'cancelled', 'name' => 'Cancelled', 'workflow' => 'pending,processing,complete'],
        ],
        'transaction' => [
            ['alias' => 'open', 'name' => 'Open', 'workflow' => 'in_progress,paid,paid_partially,cancelled,reminded,failed'],
            ['alias' => 'reminded', 'name' => 'Reminded', 'workflow' => 'in_progress,paid,paid_partially,cancelled,open'],
            ['alias' => 'in_progress', 'name' => 'In progress', 'workflow' => 'open,failed,cancelled,paid,paid_partially'],
            ['alias' => 'authorized', 'name' => 'Authorized', 'workflow' => 'paid,paid_partially,failed,cancelled'],
            ['alias' => 'paid_partially', 'name' => 'Paid (partially)', 'workflow' => 'in_progress,reminded,paid,refunded_partially,refunded,cancelled,chargeback,open'],
            ['alias' => 'paid', 'name' => 'Paid', 'workflow' => 'refunded_partially,refunded,cancelled,chargeback,open', 'paid' => '1'],
            ['alias' => 'refunded_partially', 'name' => 'Refunded (partially)', 'workflow' => 'refunded,cancelled,open'],
            ['alias' => 'refunded', 'name' => 'Refunded', 'workflow' => 'cancelled,open'],
            ['alias' => 'chargeback', 'name' => 'Chargeback', 'workflow' => 'paid,paid_partially,cancelled'],
            ['alias' => 'cancelled', 'name' => 'Cancelled', 'workflow' => 'open,refunded_partially,refunded'],
            ['alias' => 'failed', 'name' => 'Failed', 'workflow' => 'open,in_progress,paid,paid_partially'],
        ],
        'delivery' => [
            ['alias' => 'open', 'name' => 'Open', 'workflow' => 'shipped,shipped_partially,cancelled'],
            ['alias' => 'shipped', 'name' => 'Shipped', 'workflow' => 'open,cancelled,returned,returned_partially'],
            ['alias' => 'shipped_partially', 'name' => 'Shipped (partially)', 'workflow' => 'open,shipped,cancelled,returned,returned_partially'],
            ['alias' => 'cancelled', 'name' => 'Cancelled', 'workflow' => 'open'],
            ['alias' => 'returned', 'name' => 'Returned', 'workflow' => 'open'],
            ['alias' => 'returned_partially', 'name' => 'Returned (partially)', 'workflow' => 'open'],
        ],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tableExists('tl_iso_orderstatus')) {
            return true;
        }

        $table = $schemaManager->introspectTableByUnquotedName('tl_iso_orderstatus');

        if (!$table->hasColumn('type') || !$table->hasColumn('alias')) {
            return true;
        }

        return [] !== $this->getMissingTypes();
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tableExists('tl_iso_orderstatus')) {
            $this->connection->executeStatement("CREATE TABLE tl_iso_orderstatus (id INT UNSIGNED AUTO_INCREMENT NOT NULL, tstamp INT UNSIGNED DEFAULT 0 NOT NULL, type VARCHAR(16) DEFAULT 'order' NOT NULL, alias VARCHAR(32) DEFAULT '' NOT NULL, name VARCHAR(255) DEFAULT '' NOT NULL, label VARCHAR(255) DEFAULT '' NOT NULL, workflow TEXT NULL, color VARCHAR(6) DEFAULT '' NOT NULL, paid CHAR(1) DEFAULT '' NOT NULL, welcomescreen CHAR(1) DEFAULT '' NOT NULL, INDEX paid (paid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

            // Recreate the schema manager to get the new table
            $schemaManager = $this->connection->createSchemaManager();
        }

        $table = $schemaManager->introspectTableByUnquotedName('tl_iso_orderstatus');

        if (!$table->hasColumn('type')) {
            $this->connection->executeStatement("ALTER TABLE tl_iso_orderstatus ADD COLUMN `type` varchar(16) NOT NULL default 'order'");
        }

        if (!$table->hasColumn('workflow')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_orderstatus ADD COLUMN `workflow` text NULL');
        }

        if (!$table->hasColumn('alias')) {
            $this->addAliases();
        }

        $this->addMissingTypes();
        $this->migrateOrders($schemaManager);

        return $this->createResult(true);
    }

    private function getMissingTypes(): array
    {
        return array_diff(
            array_keys(self::DEFAULTS),
            $this->connection->fetchFirstColumn('SELECT DISTINCT type FROM tl_iso_orderstatus'),
        );
    }

    private function addMissingTypes(): void
    {
        $types = $this->getMissingTypes();

        foreach ($types as $type) {
            foreach (self::DEFAULTS[$type] as $status) {
                $this->connection->insert('tl_iso_orderstatus', [
                    'tstamp' => time(),
                    'type'=> $type,
                    ...$status,
                ]);
            }
        }
    }

    private function addAliases(AbstractSchemaManager $schemaManager): void
    {
        $this->connection->executeStatement("ALTER TABLE tl_iso_orderstatus ADD COLUMN `alias` varchar(32) NOT NULL default ''");

        if ($schemaManager->introspectTableByUnquotedName('tl_iso_orderstatus')->hasColumn('sorting')) {
            $records = $this->connection->fetchAllAssociative('SELECT id, name FROM tl_iso_orderstatus ORDER BY sorting');
        } else {
            $records = $this->connection->fetchAllAssociative('SELECT id, name FROM tl_iso_orderstatus');
        }


        $records = array_map(
            static fn (array $row) => [...$row, 'alias' => StringUtil::standardize($row['name'])],
            $records,
        );

        $workflow = array_column($records, 'alias');

        foreach ($records as $row) {
            $this->connection->update(
                'tl_iso_orderstatus',
                ['alias' => $row['alias'], 'workflow' => implode(',', array_diff($workflow, [$row['alias']]))],
                ['id' => $row['id']],
            );
        }
    }

    private function migrateOrders(AbstractSchemaManager $schemaManager): void
    {
        if (!$schemaManager->tableExists('tl_iso_product_collection')) {
            return;
        }

        $table = $schemaManager->introspectTableByUnquotedName('tl_iso_product_collection');

        if (!$table->getColumn('order_status')->getType() instanceof StringType) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product_collection CHANGE COLUMN `order_status` `order_status` VARCHAR(32) DEFAULT NULL');
            $this->connection->executeStatement('UPDATE tl_iso_product_collection o SET o.order_status=(SELECT s.alias FROM tl_iso_orderstatus s WHERE o.order_status=s.id)');
        }

        if (!$table->hasColumn('transaction_status')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product_collection ADD COLUMN `transaction_status` VARCHAR(32) DEFAULT NULL');

            // Set existing orders to paid if their order_status has tl_iso_orderstatus.paid
            $this->connection->executeStatement("UPDATE tl_iso_product_collection SET transaction_status='open' WHERE type='order'");
            $this->connection->executeStatement("UPDATE tl_iso_product_collection SET transaction_status='paid' WHERE type='order' AND order_status IN (SELECT alias FROM tl_iso_orderstatus WHERE type='order' AND paid=1)");
        }

        if (!$table->hasColumn('delivery_status')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product_collection ADD COLUMN `delivery_status` VARCHAR(32) DEFAULT NULL');
            $this->connection->executeStatement("UPDATE tl_iso_product_collection SET transaction_status='shipped' WHERE type='order'");
        }
    }
}
