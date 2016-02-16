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

use Isotope\Model\Attribute;

class To0020040000 extends Base
{
    public function run($blnInstalled)
    {
        $table = Attribute::getTable();

        if ($blnInstalled) {
            if ($this->createDatabaseField('checkoutTargetFolder', $table)) {
                \Database::getInstance()
                         ->prepare("UPDATE $table SET checkoutTargetFolder=?")
                         ->execute($GLOBALS['TL_DCA'][$table]['fields']['checkoutTargetFolder']['default'])
                ;
            }

            if ($this->createDatabaseField('checkoutTargetFile', $table)) {
                \Database::getInstance()
                         ->prepare("UPDATE $table SET checkoutTargetFile=?")
                         ->execute($GLOBALS['TL_DCA'][$table]['fields']['checkoutTargetFile']['default'])
                ;
            }
        }
    }
}
