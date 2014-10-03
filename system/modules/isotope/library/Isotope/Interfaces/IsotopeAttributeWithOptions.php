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

use Isotope\Collection\AttributeOption;

interface IsotopeAttributeWithOptions extends IsotopeAttribute
{

    /**
     * Adjust the attribute option wizard for this widget
     *
     * @param \Widget $objWidget
     * @param array   $arrColumns
     *
     * @return array
     */
    public function prepareOptionsWizard($objWidget, $arrColumns);

    /**
     * Get field options
     *
     * @param IsotopeProduct $objProduct
     *
     * @return array
     */
    public function getOptionsForWidget(IsotopeProduct $objProduct = null);

    /**
     * Get AttributeOption models for current attribute
     *
     * @param IsotopeProduct $objProduct
     *
     * @return AttributeOption
     */
    public function getOptionsFromManager(IsotopeProduct $objProduct = null);

    /**
     * Get a list of options for the frontend product filter
     *
     * @param array $arrValues
     *
     * @return array
     */
    public function getOptionsForProductFilter(array $arrValues);

    /**
     * Return true if attribute can have prices
     *
     * @return bool
     */
    public function canHavePrices();
}
