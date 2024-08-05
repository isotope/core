<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductAction;

use Isotope\Interfaces\IsotopeProduct;

interface ProductActionInterface
{
    /**
     * Gets the internal name of the action.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the label for the action.
     *
     * @param IsotopeProduct|null $product
     *
     * @return string
     */
    public function getLabel(IsotopeProduct $product = null);

    /**
     * Returns whether the action is available for the given product.
     *
     *
     * @return bool
     */
    public function isAvailable(IsotopeProduct $product, array $config = []);

    /**
     * Generates HTML content for this action.
     *
     *
     * @return string
     */
    public function generate(IsotopeProduct $product, array $config = []);

    /**
     * Handles submit of the product. Must self-check if this action applies.
     *
     *
     * @return bool
     */
    public function handleSubmit(IsotopeProduct $product, array $config = []);
}
