<?php

namespace Isotope\Upgrade;

use Contao\Database;

class To0020070001 extends Base
{
    public function run($blnInstalled)
    {
        $db = Database::getInstance();

        $this->migrateProductCollectionLog($db);
    }

    private function migrateProductCollectionLog(Database $db)
    {
        if (!$db->tableExists('tl_iso_product_collection', null, true)
            || !$db->tableExists('tl_iso_product_collection_log', null, true)
            || !$db->fieldExists('order_status', 'tl_iso_product_collection_log', null, true)
        ) {
            return;
        }

        $this->createDatabaseField('data', 'tl_iso_product_collection_log');

        $records = $db->execute("SELECT * FROM tl_iso_product_collection_log")->fetchAllAssoc();

        foreach ($records as $record) {
            if ($record['data']) {
                continue;
            }

            $data = [
                'sendNotification' => $record['sendNotification'],
                'notification_shipping_tracking' => $record['notification_shipping_tracking'],
                'notification_customer_notes' => $record['notification_customer_notes'],
                'notification' => $record['notification'],
                'notes' => $record['notes'],
                'date_shipped' => $record['date_shipped'],
                'date_paid' => $record['date_paid'],
                'order_status' => $record['order_status'],
            ];

            $db
                ->prepare("UPDATE tl_iso_product_collection_log SET data=? WHERE id=?")
                ->execute(json_encode($data), $record['id'])
            ;
        }

        $db->query("ALTER TABLE tl_iso_product_collection_log DROP sendNotification");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP notification_shipping_tracking");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP notification_customer_notes");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP notification");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP notes");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP date_shipped");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP date_paid");
        $db->query("ALTER TABLE tl_iso_product_collection_log DROP order_status");
    }
}
