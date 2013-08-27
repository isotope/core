<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;


/**
 * IsotopeProduct is the interface for a product object
 */
interface IsotopeProduct
{

    /**
     * Returns true if the product is available in the frontend
     * @return bool
     */
    public function isAvailableInFrontend();

    /**
     * Returns true if the product is available in the given collection
     * @return bool
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection);

    /**
     * Returns true if the product is locked (price should not be calculated, e.g. in orders), otherwise returns false
     * @return bool
     */
    public function isLocked();

    /**
     * Returns true if the product is published, otherwise returns false
     * @bool
     */
    public function isPublished();

    /**
     * Generate a product template
     * @param   array
     * @return  string
     */
    public function generate(array $arrConfig);
}
