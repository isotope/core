<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Module;

use Contao\Backend;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\Interfaces\IsotopeFilterModule;


class Callback extends Backend
{

    /**
     * Load tl_iso_product data container and language file
     */
    public function __construct()
    {
        parent::__construct();

        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');
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
        $objModules = Database::getInstance()->execute("SELECT id, name FROM tl_module WHERE type='login'");

        while ($objModules->next()) {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }

    /**
     * Get all filter modules and return them as array
     * @param DataContainer
     * @return array
     */
    public function getFilterModules(DataContainer $dc)
    {
        $arrClasses = array();

        foreach ($GLOBALS['FE_MOD'] as $arrModules) {
            foreach ($arrModules as $strName => $strClass) {
                if ($strClass != '' && !class_exists($strClass)) {
                    continue;
                }

                $objReflection = new \ReflectionClass($strClass);
                if ($objReflection->implementsInterface(IsotopeFilterModule::class)) {
                    $arrClasses[] = $strName;
                }
            }
        }

        foreach (System::getContainer()->get('isotope.fragment.registry')->all() as $name => $config) {
            if (System::getContainer()->get($config->getController()) instanceof IsotopeFilterModule) {
                $arrClasses[] = substr($name, strrpos($name, '.')+1);
            }
        }

        $arrModules = array();
        $objModules = Database::getInstance()->execute("SELECT * FROM tl_module WHERE type IN ('" . implode("','", $arrClasses) . "')");

        while ($objModules->next()) {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }
}
