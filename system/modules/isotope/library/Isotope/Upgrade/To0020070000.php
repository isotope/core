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

class To0020070000 extends Base
{
    public function run($blnInstalled)
    {
        $db = Database::getInstance();

        if (!$db->tableExists('tl_iso_product_collection')
            || $db->tableExists('tl_iso_product_collection_log')
        ) {
            return;
        }

        $db->execute("
CREATE TABLE tl_iso_product_collection_log (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
  tstamp INT UNSIGNED DEFAULT 0 NOT NULL, 
  pid INT UNSIGNED DEFAULT 0 NOT NULL, 
  author INT UNSIGNED DEFAULT 0 NOT NULL, 
  order_status INT UNSIGNED DEFAULT 0 NOT NULL, 
  date_paid INT DEFAULT NULL, 
  date_shipped INT DEFAULT NULL, 
  notes TEXT DEFAULT NULL, 
  sendNotification CHAR(1) DEFAULT '' NOT NULL, 
  notification INT UNSIGNED DEFAULT 0 NOT NULL, 
  notification_shipping_tracking TEXT DEFAULT NULL, 
  notification_customer_notes TEXT DEFAULT NULL, 
  INDEX pid (pid), 
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM
        ");

        $db->execute("
INSERT INTO tl_iso_product_collection_log (tstamp, pid, order_status, date_paid, date_shipped, notes) 
SELECT tstamp, id, order_status, date_paid, date_shipped, notes
FROM tl_iso_product_collection
WHERE type='order' AND locked IS NOT NULL   
        ");
    }
}
