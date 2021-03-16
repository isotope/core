<?php
/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2017 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

use Contao\Database;

class To0020080000 extends Base
{
    public function run($blnInstalled)
    {
        $db = Database::getInstance();

        $this->migrateProductCollectionLog($db);
    }

    private function migrateProductCollectionLog(Database $db)
    {
        if (!$db->tableExists('tl_iso_product_collection')
            || !$db->tableExists('tl_iso_product_collection_log')
            || !$db->fieldExists('order_status', 'tl_iso_product_collection_log')
        ) {
            return;
        }

        $this->createDatabaseField('data', 'tl_iso_product_collection_log');

        $records = $db->execute("SELECT * FROM tl_iso_product_collection_log");

        while ($records->next()) {
            if ($records->data) {
                continue;
            }

            $data = [
                'sendNotification' => $records->sendNotification,
                'notification_shipping_tracking' => $records->notification_shipping_tracking,
                'notification_customer_notes' => $records->notification_customer_notes,
                'notification' => $records->notification,
                'notes' => $records->notes,
                'date_shipped' => $records->date_shipped,
                'date_paid' => $records->date_paid,
                'order_status' => $records->order_status,
            ];

            $db
                ->prepare("UPDATE tl_iso_product_collection_log SET data=? WHERE id=?")
                ->execute(json_encode($data), $records->id)
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
