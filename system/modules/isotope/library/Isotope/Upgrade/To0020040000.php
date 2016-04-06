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

use Contao\FilesModel;
use Isotope\Model\Attribute;

class To0020040000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        $db = \Database::getInstance();
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
}
