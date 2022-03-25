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
use Contao\FilesModel;
use Isotope\Model\Attribute;

class To0020040000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        $this->migrateUploadAttributes();
        $this->migrateCollectionDates();
    }

    private function migrateUploadAttributes()
    {
        $db = Database::getInstance();
        $table = Attribute::getTable();

        if ($this->createDatabaseField('checkoutTargetFolder', $table)) {
            $db
                ->prepare("UPDATE $table SET checkoutTargetFolder=?")
                ->execute($GLOBALS['TL_DCA'][$table]['fields']['checkoutTargetFolder']['default'])
            ;
        }

        if ($this->createDatabaseField('checkoutTargetFile', $table)) {
            $db
                ->prepare("UPDATE $table SET checkoutTargetFile=?")
                ->execute($GLOBALS['TL_DCA'][$table]['fields']['checkoutTargetFile']['default'])
            ;
        }

        $uploads = $db->execute("SELECT * FROM tl_iso_attribute WHERE type='upload' AND storeFile='1'");

        while ($uploads->next()) {
            $target = $GLOBALS['TL_DCA'][$table]['fields']['checkoutTargetFolder']['default'];

            if (($uploadFolder = FilesModel::findByPk($uploads->uploadFolder)) !== null) {
                $target = $uploadFolder->path;
            }

            if ($uploads->useHomeDir) {
                $target = sprintf(
                    '{if has_member="1"}{if member_homeDir!=""}##member_homeDir##{else}%s{endif}{else}%s{endif}',
                    $target,
                    $target
                );
            }

            $db
                ->prepare("UPDATE tl_iso_attribute SET checkoutTargetFolder=?, storeFile='' WHERE id=?")
                ->execute($target, $uploads->id)
            ;
        }
    }

    private function migrateCollectionDates()
    {
        $db = Database::getInstance();

        $db->query('
            ALTER TABLE tl_iso_product_collection
            CHANGE COLUMN `date_paid` `date_paid` VARCHAR(10) NULL,
            CHANGE COLUMN `date_shipped` `date_shipped` VARCHAR(10) NULL,
            CHANGE COLUMN `locked` `locked` VARCHAR(10) NULL
        ');

        $db->query("UPDATE tl_iso_product_collection SET date_paid=NULL WHERE date_paid=''");
        $db->query("UPDATE tl_iso_product_collection SET date_shipped=NULL WHERE date_shipped=''");
        $db->query("UPDATE tl_iso_product_collection SET locked=NULL WHERE locked=''");

        $db->query('
            ALTER TABLE tl_iso_product_collection
            CHANGE COLUMN `date_paid` `date_paid` INT(10) NULL,
            CHANGE COLUMN `date_shipped` `date_shipped` INT(10) NULL,
            CHANGE COLUMN `locked` `locked` INT(10) NULL
        ');
    }
}
