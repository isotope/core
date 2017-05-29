<?php
/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2017 terminal42 gmbh & Isotope eCommerce Workgroup
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

class To0020050000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        if ($this->updateDatabaseField('price', 'tl_iso_payment')) {
            \Database::getInstance()->query(
                "UPDATE tl_iso_payment SET price=NULL WHERE price='' OR price REGEXP '^0(\.[0]*)?$'"
            );
        }

        if ($this->updateDatabaseField('price', 'tl_iso_shipping')) {
            \Database::getInstance()->query(
                "UPDATE tl_iso_shipping SET price=NULL WHERE price='' OR price REGEXP '^0(\.[0]*)?$'"
            );
        }
    }
}
