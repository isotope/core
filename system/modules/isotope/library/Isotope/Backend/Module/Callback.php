<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Module;

use Isotope\Model\Payment;
use Isotope\Model\Shipping;


class Callback extends \Backend
{

    /**
     * Load tl_iso_product data container and language file
     */
    public function __construct()
    {
        parent::__construct();

        \Controller::loadDataContainer('tl_iso_product');
        \System::loadLanguageFile('tl_iso_product');
    }


    /**
     * Get the attribute filter fields and return them as array
     * @return array
     */
    public function getFilterFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ($arrData['attributes']['fe_filter']) {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Get the attribute sorting fields and return them as array
     * @return array
     */
    public function getSortingFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ($arrData['attributes']['fe_sorting']) {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Get the attribute search fields and return them as array
     * @return array
     */
    public function getSearchFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ($arrData['attributes']['fe_search']) {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Get the attribute autocomplete fields and return them as array
     * @return array
     */
    public function getAutocompleteFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ($arrData['attributes']['fe_search'] && !$arrData['attributes']['dynamic']) {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Returns a list of all payment modules
     * @return array
     */
    public function getPaymentModules()
    {
        $t = Payment::getTable();
        $objPayment = Payment::findBy(array("$t.tstamp>0"), null);

        if (null === $objPayment) {
            return array();
        }

        return $objPayment->fetchEach('name');
    }


    /**
     * Get all enabled shipping modules and return them as array
     * @return array
     */
    public function getShippingModules()
    {
        $t = Shipping::getTable();
        $objShipping = Shipping::findBy(array("$t.tstamp>0"), null);

        if (null === $objShipping) {
            return array();
        }

        return $objShipping->fetchEach('name');
    }


    /**
     * Get all login modules and return them as array
     * @return array
     */
    public function getLoginModuleList()
    {
        $arrModules = array();
        $objModules = \Database::getInstance()->execute("SELECT id, name FROM tl_module WHERE type='login'");

        while ($objModules->next()) {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }


    /**
     * Get all buttons and return them as array
     * @return array
     */
    public function getButtons()
    {
        $arrOptions = array();
        $arrButtons = array();

        // !HOOK: add product buttons
        if (isset($GLOBALS['ISO_HOOKS']['buttons']) && is_array($GLOBALS['ISO_HOOKS']['buttons'])) {
            foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrButtons  = $objCallback->{$callback[1]}($arrButtons);
            }
        }

        foreach ($arrButtons as $button => $data) {
            $arrOptions[$button] = $data['label'];
        }

        return $arrOptions;
    }


    /**
     * Return filter templates as array
     * @param DataContainer
     * @return array
     */
    public function getFilterTemplates()
    {
        return \Isotope\Backend::getTemplates('iso_filter_');
    }


    /**
     * Get all filter modules and return them as array
     * @param DataContainer
     * @return array
     */
    public function getFilterModules(\DataContainer $dc)
    {
        $arrClasses = array();

        foreach ($GLOBALS['FE_MOD'] as $arrModules) {
            foreach ($arrModules as $strName => $strClass) {
                if ($strClass != '' && !class_exists($strClass)) {
                    continue;
                }

                $objReflection = new \ReflectionClass($strClass);
                if ($objReflection->implementsInterface('Isotope\Interfaces\IsotopeFilterModule')) {
                    $arrClasses[] = $strName;
                }
            }
        }

        $arrModules = array();
        $objModules = \Database::getInstance()->execute("SELECT * FROM tl_module WHERE type IN ('" . implode("','", $arrClasses) . "')");

        while ($objModules->next()) {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }
}
