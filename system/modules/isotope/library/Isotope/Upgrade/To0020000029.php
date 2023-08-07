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

class To0020000029 extends System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            Database::getInstance()->query("
                UPDATE tl_iso_product_collection_download
                SET expires=(tstamp+expires)
                WHERE expires!='' AND expires<tstamp
            ");
        }
    }
}
