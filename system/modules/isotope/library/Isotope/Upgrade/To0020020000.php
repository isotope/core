<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


class To0020020000 extends Base
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            $this->createDatabaseField('optionsSource', 'tl_iso_attributes');

            \Database::getInstance()->query("UPDATE tl_iso_attribute SET optionsSource='foreignKey' WHERE foreignKey!=''");
            \Database::getInstance()->query("UPDATE tl_iso_attribute SET optionsSource='attribute' WHERE optionsSource=''");

            $this->renameDatabaseField('options', 'configuration', 'tl_iso_product_collection_item');
        }
    }
}
