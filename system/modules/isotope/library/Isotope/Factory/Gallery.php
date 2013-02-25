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

use Isotope\Isotope;


class Gallery
{

    /**
     * Cache of gallery classes
     * @var array
     */
    private static $arrClasses;

    /**
     * Build a gallery based on given data
     * @param  array
     * @return Isotope\Interface\IsotopeGallery
     */
    public static function build($strClass, $strName, $arrFiles)
    {
        // Try config class if none is given
        if ($strClass == '' || !class_exists('\Isotope\Gallery\\' . $strClass)) {
            $strClass = Isotope::getInstance()->Config->gallery;
        }

        // Use Standard class if no other is available
        if ($strClass == '' || !class_exists('\Isotope\Gallery\\' . $strClass)) {
            $strClass = 'Standard';
        }

        $strClass = '\Isotope\Gallery\\' . $strClass;

        return new $strClass($strName, $arrFiles);
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

            if (is_array($arrNamespaces['Isotope/Gallery'])) {
                foreach ($arrNamespaces['Isotope/Gallery'] as $strPath) {
                    foreach (scan($strPath . '/Isotope/Gallery') as $strFile) {

                        $strClass = pathinfo($strFile, PATHINFO_FILENAME);
                        $strNamespacedClass = '\Isotope\Gallery\\' . $strClass;

                        if (is_a($strNamespacedClass, 'Isotope\Interfaces\IsotopeGallery', true)) {
                            static::$arrClasses[$strClass] = $strNamespacedClass;
                        }
                    }
                }
            }
        }

        return static::$arrClasses;
    }

    /**
     * Return labels for all galleries
     * @return array
     */
    public static function getClassLabels()
    {
        $arrLabels = array();

        foreach (static::getCLasses() as $strClass => $strNamespacedClass) {
            $arrLabels[$strClass] = call_user_func(array($strNamespacedClass, 'getClassLabel'));
        }

        return $arrLabels;
    }
}
