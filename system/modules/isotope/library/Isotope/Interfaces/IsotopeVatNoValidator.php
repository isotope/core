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

namespace Isotope\Interfaces;

use Isotope\Model\Address;

interface IsotopeVatNoValidator
{

    /**
     * Return true if vat number could be validated, false if not
     *
     * @return bool
     * @throws \RuntimeException to add a custom error message (e.g. to the form field)
     */
    public function validate(Address $objAddress);
}
