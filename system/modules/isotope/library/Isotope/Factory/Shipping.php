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

namespace Isotope\Factory;


class Shipping
{

    /**
     * Cache of shipping method classes
     * @var array
     */
    private static $arrClasses;

    /**
     * Build a shipping method based on row data
     * @param  string
     * @param  array
     * @return Isotope\Interface\IsotopeShipping
     */
    public static function build($strClass, array $arrData=array())
    {
        $strClass = '\Isotope\Shipping\\' . $strClass;

        return new $strClass($arrData);
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

            if (is_array($arrNamespaces['Isotope/Shipping'])) {
                foreach ($arrNamespaces['Isotope/Shipping'] as $strPath) {
                    foreach (scan($strPath . '/Isotope/Shipping') as $strFile) {

                        $strClass = pathinfo($strFile, PATHINFO_FILENAME);
                        $strNamespacedClass = '\Isotope\Shipping\\' . $strClass;

                        if (is_a($strNamespacedClass, 'Isotope\Interfaces\IsotopeShipping', true)) {
                            static::$arrClasses[$strClass] = $strNamespacedClass;
                        }
                    }
                }
            }
        }

        return static::$arrClasses;
    }

    /**
     * Return labels for all shipping methods
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
