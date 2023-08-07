<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

use Contao\Database;

class To0020030029 extends Base
{
    /**
     * @var Database
     */
    private $db;

    public function run($blnInstalled)
    {
        $this->db = Database::getInstance();

        if ($blnInstalled) {
            $collections = $this->db->execute(
                "SELECT uniqid, COUNT(id) AS total
                FROM tl_iso_product_collection
                WHERE uniqid IS NOT NULL
                GROUP BY uniqid
                HAVING total>1"
            );

            while ($collections->next()) {
                $this->db
                    ->prepare("UPDATE tl_iso_product_collection SET uniqid=NULL WHERE uniqid=?")
                    ->execute($collections->uniqid)
                ;
            }
        }
    }
}
