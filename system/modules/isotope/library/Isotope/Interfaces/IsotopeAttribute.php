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

/**
 * IsotopeAttribute is a product attribute for Isotope eCommerce
 */
interface IsotopeAttribute
{
    /**
     * Returns true if attribute is customer defined
     *
     * @return bool
     */
    public function isCustomerDefined();

    /**
     * Returns the database field name
     *
     * @return string
     */
    public function getFieldName();

    /**
     * Return class name for the backend widget or empty if none should be available
     *
     * @return string
     */
    public function getBackendWidget();

    /**
     * Return class name for the frontend widget or empty if none should be available
     *
     * @return string
     */
    public function getFrontendWidget();

    /**
     * Load attribute configuration from given DCA array
     *
     * @param   array
     */
    public function loadFromDCA(array &$arrData, $strName);

    /**
     * Save attribute configuration into the given DCA array
     *
     * @param array $arrData
     */
    public function saveToDCA(array &$arrData);

    /**
     * Gets attribute value from given product.
     *
     * @param IsotopeProduct $product
     *
     * @return mixed
     */
    public function getValue(IsotopeProduct $product);

    /**
     * Gets attribute label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Generate attribute for given product
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrOptions
     *
     * @return string
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array());
}
