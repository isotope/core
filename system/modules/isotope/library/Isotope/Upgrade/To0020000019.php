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
use Contao\System;

class To0020000019 extends System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled && Database::getInstance()->tableExists('tl_iso_rule')) {
            foreach (Database::getInstance()->listFields('tl_iso_rule') as $arrField) {

                if ('configCondition' === $arrField['name'] && 'char' === $arrField['type']) {
                    Database::getInstance()->query("UPDATE tl_iso_rule SET configCondition='0' WHERE configCondition='1'");
                    Database::getInstance()->query("UPDATE tl_iso_rule SET configCondition='1' WHERE configCondition=''");
                    Database::getInstance()->query("ALTER TABLE tl_iso_rule CHANGE COLUMN `configCondition` `configCondition` tinyint(1) NOT NULL default '0'");
                }

                if ('memberCondition' === $arrField['name'] && 'char' === $arrField['type']) {
                    Database::getInstance()->query("UPDATE tl_iso_rule SET memberCondition='0' WHERE memberCondition='1'");
                    Database::getInstance()->query("UPDATE tl_iso_rule SET memberCondition='1' WHERE memberCondition=''");
                    Database::getInstance()->query("ALTER TABLE tl_iso_rule CHANGE COLUMN `memberCondition` `memberCondition` tinyint(1) NOT NULL default '0'");
                }

                if ('productCondition' === $arrField['name'] && 'char' === $arrField['type']) {
                    Database::getInstance()->query("UPDATE tl_iso_rule SET productCondition='0' WHERE productCondition='1'");
                    Database::getInstance()->query("UPDATE tl_iso_rule SET productCondition='1' WHERE productCondition=''");
                    Database::getInstance()->query("ALTER TABLE tl_iso_rule CHANGE COLUMN `productCondition` `productCondition` tinyint(1) NOT NULL default '0'");
                }
            }
        }
    }
}
