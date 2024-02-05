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

interface IsotopeAttributeWithRange extends IsotopeAttribute
{
    /**
     * Returns whether range filter can be used on this attribute.
     *
     * @return bool
     */
    public function allowRangeFilter();

    /**
     * Gets array of values for range filter.
     *
     *
     * @return array
     */
    public function getValueRange(IsotopeProduct $product);
}
