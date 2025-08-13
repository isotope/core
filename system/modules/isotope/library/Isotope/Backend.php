<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Backend as ContaoBackend;
use Contao\Controller;
use Contao\Input;
use Contao\System;
use Contao\Widget;
use Isotope\Backend\Product\Permission;
use Isotope\Model\Config;

/**
 * Provide methods to handle Isotope back end components.
 */
class Backend extends ContaoBackend
{
    /**
     * Get array of subdivisions, delay loading of file if not necessary
     *
     * @return array
     */
    public static function getSubdivisions()
    {
        static $arrSubdivisions = null;

        if (null === $arrSubdivisions) {

            System::loadLanguageFile('subdivisions');

            foreach ($GLOBALS['TL_LANG']['DIV'] as $strCountry => $arrSubdivision) {
                foreach ($arrSubdivision as $strCode => $varValue) {
                    if (\is_array($varValue)) {
                        $strGroup = $varValue[''];
                        unset($varValue['']);

                        $arrSubdivisions[$strCountry][$strCode][$strGroup] = $varValue;

                        continue;
                    }

                    $arrSubdivisions[$strCountry][$strCode] = $varValue;
                }
            }
        }

        return $arrSubdivisions;
    }

    /**
     * Returns the label for a subdivision of a country.
     *
     * @param string $country
     * @param string $subdivision
     *
     * @return string
     */
    public static function getLabelForSubdivision($country, $subdivision)
    {
        $country = strtolower($country);
        $arrSubdivisions = Backend::getSubdivisions();

        if (isset($arrSubdivisions[$country][$subdivision])) {
            return $arrSubdivisions[$country][$subdivision];
        }

        if (\is_array($arrSubdivisions[$country])) {
            foreach ($arrSubdivisions[$country] as $regionGroup) {
                if (\is_array($regionGroup)) {
                    foreach ($regionGroup as $regions) {
                        if (isset($regions[$subdivision])) {
                            return $regions[$subdivision];
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * List template from all themes, show theme name
     *
     * @param string $strPrefix
     *
     * @return array
     */
    public static function getTemplates($strPrefix)
    {
        $arrTemplates = Controller::getTemplateGroup($strPrefix);

        // Try to select the shop configs
        try {
            $objConfig = Config::findAll(array('order' => 'name'));
        } catch (\Exception $e) {
            $objConfig = null;
        }

        // Add the shop config templates
        if (null !== $objConfig) {
            while ($objConfig->next()) {
                if ($objConfig->templateGroup != '') {
                    $arrConfigTemplates = glob(\Contao\System::getContainer()->getParameter('kernel.project_dir') . '/' . $objConfig->templateGroup . '/' . $strPrefix . '*');

                    if (\is_array($arrConfigTemplates)) {
                        foreach ($arrConfigTemplates as $strFile) {

                            $strTemplate = basename($strFile, strrchr($strFile, '.'));

                            if (!isset($arrTemplates[$strTemplate])) {
                                $arrTemplates[$strTemplate] = $strTemplate;
                            } else {
                                $arrTemplates[$strTemplate] = substr($arrTemplates[$strTemplate], 0, -1) . ', ' . sprintf($GLOBALS['TL_LANG']['MSC']['templatesConfig'], $objConfig->name) . ')';
                            }
                        }
                    }
                }
            }
        }

        return $arrTemplates;
    }

    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     *
     * @return array|bool
     *
     * @deprecated will be removed in Isotope 3.0
     */
    public static function getAllowedProductIds()
    {
        return Permission::getAllowedIds();
    }

    /**
     * Load type agent model help
     *
     * @param string $strTable
     */
    public function loadTypeAgentHelp($strTable)
    {
        $strKey = Input::get('table');
        $strField = Input::get('field');

        if ($strKey !== $strTable) {
            return;
        }

        if ('tl_iso_producttype' === $strKey) {
            $strKey = 'tl_iso_product';
        }

        //if (
        //    TL_SCRIPT !== 'contao/help.php' ||
        //    !isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]) ||
        //    !is_subclass_of(Model::getClassFromTable($strKey), TypeAgent::class)
        //) {
            return;
        //}

        $arrField = &$GLOBALS['TL_DCA'][$strTable]['fields'][$strField];

        // Get the field type
        /** @var Widget $strClass */
        $strClass = $GLOBALS['BE_FFL'][$arrField['inputType']];

        // Abort if the class is not defined
        if (!class_exists($strClass)) {
            return;
        }

        $arrFieldComplete = $strClass::getAttributesFromDca($arrField, $strField);

        if (
            !$arrFieldComplete ||
            !$arrFieldComplete['helpwizard'] ||
            !\is_array($arrFieldComplete['options']) ||
            $arrField['explanation'] != '' ||
            isset($GLOBALS['TL_LANG']['XPL']['type'])
        ) {

            return;
        }

        // try to load a type agent model help description
        $arrField['explanation'] = 'type';
        foreach ($arrFieldComplete['options'] as $arrOption) {
            $arrLabel = $GLOBALS['TL_LANG']['MODEL'][$strKey][$arrOption['value']];
            if ($arrLabel) {
                $GLOBALS['TL_LANG']['XPL']['type'][] = $arrLabel;
            }
        }
    }
}
