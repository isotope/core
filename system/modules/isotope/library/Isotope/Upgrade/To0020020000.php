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

class To0020020000 extends Base
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            $this->createDatabaseField('optionsSource', 'tl_iso_attribute');
            $this->createDatabaseField('includeBlankOption', 'tl_iso_attribute');

            Database::getInstance()->query("UPDATE tl_iso_attribute SET optionsSource='foreignKey', includeBlankOption='1' WHERE foreignKey!=''");
            Database::getInstance()->query("UPDATE tl_iso_attribute SET optionsSource='attribute' WHERE optionsSource=''");

            $this->renameDatabaseField('options', 'configuration', 'tl_iso_product_collection_item');
        }
    }
}
