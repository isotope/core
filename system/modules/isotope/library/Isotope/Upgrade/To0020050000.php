<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
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

class To0020050000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        if ($this->updateDatabaseField('price', 'tl_iso_payment')) {
            Database::getInstance()->query(
                "UPDATE tl_iso_payment SET price=NULL WHERE price='' OR price REGEXP '^0(\.[0]*)?$'"
            );
        }

        if ($this->updateDatabaseField('price', 'tl_iso_shipping')) {
            Database::getInstance()->query(
                "UPDATE tl_iso_shipping SET price=NULL WHERE price='' OR price REGEXP '^0(\.[0]*)?$'"
            );
        }

        if ($this->createDatabaseField('iso_notifications', 'tl_module')) {
            Database::getInstance()->query("UPDATE tl_module SET iso_notifications=nc_notification");
        }
    }
}
