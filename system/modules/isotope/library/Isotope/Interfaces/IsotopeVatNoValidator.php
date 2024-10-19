<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

use Isotope\Model\Address;
use Isotope\Model\TaxRate;

interface IsotopeVatNoValidator
{

    /**
     * Return true if vat number could be validated, false if not
     *
     *
     * @return bool
     * @throws \RuntimeException to add a custom error message (e.g. to the form field)
     */
    public function validate(Address $objAddress);

    /**
     * Check if tax should be exempted because of a valid tax number
     *
     *
     * @return bool
     */
    public function exemptTax(Address $address, TaxRate $tax);
}
