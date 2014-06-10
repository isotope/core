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

interface IsotopeAttributeForVariants extends IsotopeAttributeWithOptions
{

    /**
     * Return true if attribute is a variant option
     * @return    bool
     */
    public function isVariantOption();

    /**
     * Get available variant options for a product
     * @param   array
     * @param   array
     * @return  array
     */
    public function getOptionsForVariants(array $arrIds, array $arrOptions = array());
}
