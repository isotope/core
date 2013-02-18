<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model\Collection;


class Payment extends \Model\Collection
{

    /**
     * Cache of payment method classes
     * @var array
     */
    private static $arrClasses;

    /**
     * Fetch the next result row and create the model
     *
     * @return boolean True if there was another row
     */
    protected function fetchNext()
    {
        if ($this->objResult->next() == false)
        {
            return false;
        }

        $strClass = $strClass = '\Isotope\Model\Payment\\' . $this->objResult->type;
        $this->arrModels[$this->intIndex + 1] = new $strClass($this->objResult);

        return true;
    }

    /**
     * Find all classes and cache the result
     * @return array
     */
    public static function getClasses()
    {
        if (null === static::$arrClasses) {

            static::$arrClasses = array();
            $arrNamespaces = \NamespaceClassLoader::getClassLoader()->getPrefixes();

            if (is_array($arrNamespaces['Isotope/Model/Payment'])) {
                foreach ($arrNamespaces['Isotope/Model/Payment'] as $strPath) {
                    foreach (scan($strPath . '/Isotope/Model/Payment') as $strFile) {

                        $strClass = pathinfo($strFile, PATHINFO_FILENAME);
                        $strNamespacedClass = '\Isotope\Model\Payment\\' . $strClass;

                        if (is_a($strNamespacedClass, 'Isotope\Interfaces\IsotopePayment', true)) {
                            static::$arrClasses[$strClass] = $strNamespacedClass;
                        }
                    }
                }
            }
        }

        return static::$arrClasses;
    }

    /**
     * Return labels for all payment methods
     * @return array
     */
    public static function getLabels()
    {
        $arrLabels = array();

        foreach (static::getCLasses() as $strClass => $strNamespacedClass) {
            $arrLabels[$strClass] = call_user_func(array($strNamespacedClass, 'getLabel'));
        }

        return $arrLabels;
    }
}
