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


class To0020030006 extends Base
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            // Will update the field definition
            $this->renameDatabaseField('uniqid', 'uniqid', 'tl_iso_product_collection');

            \Database::getInstance()->query("UPDATE tl_iso_product_collection SET uniqid=NULL WHERE uniqid=''");
        }
    }
}
