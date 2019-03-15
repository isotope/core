<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * @copyright  Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


class To0020000009 extends Base
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            $this->renameDatabaseField('address1_id', 'billing_address_id', 'tl_iso_product_collection');
            $this->renameDatabaseField('address2_id', 'shipping_address_id', 'tl_iso_product_collection');
        }
    }
}
