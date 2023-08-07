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

class To0020040003 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        if ($this->createDatabaseField('rounding', 'tl_iso_rule')) {
            Database::getInstance()->query("UPDATE tl_iso_rule SET rounding='down'");
        }
    }
}
