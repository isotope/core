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


class To0020020003 extends Base
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            $this->createDatabaseField('product_types_condition', 'tl_iso_shipping');
            $this->createDatabaseField('product_types_condition', 'tl_iso_payment');

            \Database::getInstance()->query("UPDATE tl_iso_shipping SET product_types_condition='onlyAvailable' WHERE product_types_condition=''");
            \Database::getInstance()->query("UPDATE tl_iso_payment SET product_types_condition='onlyAvailable' WHERE product_types_condition=''");
        }
    }
}
