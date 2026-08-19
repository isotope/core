<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

class TransactionMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_iso_product_collection', 'tl_iso_payment'])
            && !$schemaManager->tablesExist(['tl_iso_order_transaction', 'tl_iso_order_transaction_event']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement('CREATE TABLE tl_iso_order_transaction (id INT UNSIGNED AUTO_INCREMENT NOT NULL, pid INT UNSIGNED NOT NULL, tstamp INT UNSIGNED DEFAULT 0 NOT NULL, payment_id INT UNSIGNED NOT NULL, created INT UNSIGNED NOT NULL, completed INT UNSIGNED DEFAULT NULL, uniqid VARCHAR(64) NOT NULL, label TEXT DEFAULT NULL, amount TEXT DEFAULT NULL, currency TEXT DEFAULT NULL, status VARCHAR(32) DEFAULT NULL, INDEX pid (pid), UNIQUE INDEX uniqid (uniqid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->connection->executeStatement('CREATE TABLE tl_iso_order_transaction_event (id INT UNSIGNED AUTO_INCREMENT NOT NULL, pid INT UNSIGNED NOT NULL, tstamp INT UNSIGNED NOT NULL, payload TEXT NOT NULL, INDEX pid (pid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');

        $orders = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT c.*, p.name AS payment_name
            FROM tl_iso_product_collection c
            LEFT JOIN tl_iso_payment p ON c.payment_id=p.id
            WHERE c.type='order' AND c.locked IS NOT NULL
            SQL);

        foreach ($orders as $order) {
            $this->connection->insert('tl_iso_order_transaction', [
                'pid' => $order['id'],
                'tstamp' => $order['tstamp'],
                'payment_id' => (int) ($order['payment_id'] ?? 0),
                'created' => $order['tstamp'],
                'completed' => $order['tstamp'],
                'uniqid' => Uuid::v4()->toString(),
                'label' => $order['payment_name'] ?: 'ID '.$order['payment_id'],
                'amount' => $order['total'],
                'currency' => $order['currency'],
                'status' => 'paid',
            ]);

            if (null !== $order['payment_data']) {
                // TODO: create event(s) for the payment data
            }
        }

        return $this->createResult(true);
    }
}
