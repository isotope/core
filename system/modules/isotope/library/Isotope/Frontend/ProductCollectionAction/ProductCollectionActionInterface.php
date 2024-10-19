<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;

interface ProductCollectionActionInterface
{
    /**
     * Gets the internal name of the action.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the action label.
     *
     *
     * @return string
     */
    public function getLabel(IsotopeProductCollection $collection);

    /**
     * Returns whether the action is available for the given product.
     *
     *
     * @return bool
     */
    public function isAvailable(IsotopeProductCollection $collection);

    /**
     * Generates HTML content for this action.
     *
     *
     * @return string
     */
    public function generate(IsotopeProductCollection $collection);

    /**
     * Handles submit of the product collection. Must self-check if this action applies.
     *
     *
     * @return bool
     */
    public function handleSubmit(IsotopeProductCollection $collection);
}
