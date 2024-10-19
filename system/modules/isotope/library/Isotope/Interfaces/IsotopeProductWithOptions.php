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

interface IsotopeProductWithOptions
{
    /**
     * Return the product's options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Sets the product options.
     */
    public function setOptions(array $options);

    /**
     * Get customer defined field values
     *
     * @return array
     */
    public function getCustomerConfig();

    /**
     * Get variant option field values
     *
     * @return array
     */
    public function getVariantConfig();
}
