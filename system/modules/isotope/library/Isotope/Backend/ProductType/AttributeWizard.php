<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Contao\Backend;
use Contao\Controller;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Model\Attribute;
use Isotope\Model\Product;


class AttributeWizard extends Backend
{

    /**
     * Return list of MultiColumnWizard columns
     *
     * @param \MultiColumnWizard|object $objWidget
     *
     * @return array
     */
    public function getColumns($objWidget)
    {
        Controller::loadDataContainer(Product::getTable());

        $arrValues   = $objWidget->value;
        $blnVariants = $this->isVariants($objWidget->name);

        if (!empty($arrValues) && \is_array($arrValues)) {
            if ($blnVariants) {
                $arrFixed = Attribute::getVariantFixedFields($objWidget->dataContainer->activeRecord->class);
            } else {
                $arrFixed = Attribute::getFixedFields($objWidget->dataContainer->activeRecord->class);
            }

            foreach ($arrValues as $i => $attribute) {
                if (\in_array($attribute['name'], $arrFixed, true)) {
                    $objWidget->addDataToFieldAtIndex($i, 'enabled', array('eval' => array('disabled' => true)));
                }

                /** @var IsotopeAttribute|IsotopeAttributeForVariants $objAttribute */
                $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attribute['name']] ?? null;
                if (null !== $objAttribute && /* @todo in 3.0: $objAttribute instanceof IsotopeAttributeForVariants && */$objAttribute->isVariantOption()) {
                    $objWidget->addDataToFieldAtIndex($i, 'mandatory', array('eval' => array('hideBody' => true)));
                }
            }
        }

        return array
        (
            'enabled'   => array
            (
                'inputType' => 'checkbox',
                'eval'      => array('hideHead' => true),
            ),
            'name'      => array
            (
                'input_field_callback' => array('Isotope\Backend\ProductType\AttributeWizard', 'getNextName'),
                'eval'                 => array('hideHead' => true, 'tl_class' => 'mcwUpdateFields'),
            ),
            'legend'    => array
            (
                'label'            => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['legend'],
                'inputType'        => 'select',
                'options_callback' => array('Isotope\Backend\ProductType\AttributeWizard', 'getLegends'),
                'eval'             => array('style' => 'width:150px', 'class' => 'extendable'),
            ),
            'tl_class'  => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['tl_class'],
                'inputType' => 'text',
                'eval'      => array('style' => 'width:80px'),
            ),
            'mandatory' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['mandatory'],
                'inputType' => 'select',
                'options'   => array('yes', 'no'),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval'      => array('style' => 'width:80px', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['default']),
            ),
        );
    }

    /**
     * For each call, return the name of the next attribute in the wizard (for input_field_callback)
     *
     * @param Widget|object $objWidget
     *
     * @return string
     */
    public function getNextName($objWidget)
    {
        static $arrValues;
        static $strWidget;
        static $i = 0;

        if ($objWidget->name !== $strWidget || empty($arrValues)) {
            $strWidget = $objWidget->name;
            $arrValues = $objWidget->value;
            $i         = 0;
        }

        $arrField = array_shift($arrValues);
        if (null === $arrField) {
            return '';
        }

        $strName  = $arrField['name'];
        $style = '';

        /** @var IsotopeAttribute|IsotopeAttributeForVariants $objAttribute */
        $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strName] ?? null;

        if (null !== $objAttribute && $objAttribute->isVariantOption()) {
            $style = ';font-style:italic';
        }

        return sprintf(
            '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div style="width:300px%s">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>',
            $objWidget->name,
            $i,
            $objWidget->name,
            $i++,
            $strName,
            $style,
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] ?? $strName,
            $strName
        );
    }

    /**
     * Return list of default and widget legends
     *
     * @param object $objWidget The widget object
     *
     * @return array
     */
    public function getLegends($objWidget)
    {
        Controller::loadDataContainer(Attribute::getTable());
        System::loadLanguageFile(Product::getTable());

        $arrLegends = $GLOBALS['TL_DCA'][Attribute::getTable()]['fields']['legend']['options'];
        $arrLegends = array_intersect_key($GLOBALS['TL_LANG'][Product::getTable()], array_flip($arrLegends));

        $varValue = $objWidget->value;

        if (!empty($varValue) && \is_array($varValue)) {
            foreach ($varValue as $arrField) {
                if ($arrField['legend'] != '' && !isset($arrLegends[$arrField['legend']])) {
                    $arrLegends[$arrField['legend']] = $arrField['legend'];
                }
            }
        }

        return $arrLegends;
    }

    /**
     * Generate list of fields and add missing ones from DCA
     *
     * @param mixed          $varValue
     * @param DataContainer $dc
     *
     * @return array
     */
    public function load($varValue, $dc)
    {
        Controller::loadDataContainer('tl_iso_product');

        $arrDCA      = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $arrFields   = array();
        $arrValues   = StringUtil::deserialize($varValue);
        $blnVariants = $this->isVariants($dc->field);

        if ($blnVariants) {
            $arrFixed = Attribute::getVariantFixedFields($dc->activeRecord->class);
        } else {
            $arrFixed = Attribute::getFixedFields($dc->activeRecord->class);
        }

        if (!\is_array($arrValues)) {
            $arrValues = array();
        }

        foreach ($arrValues as $arrField) {

            $strName = $arrField['name'];

            if ($strName == ''
                || !isset($arrDCA[$strName])
                || $arrDCA[$strName]['attributes']['legend'] == ''
                || $this->isExcluded($strName, $blnVariants)
                || ($blnVariants && ($arrDCA[$strName]['attributes']['inherit'] ?? false))
                || (!$blnVariants && ($arrDCA[$strName]['attributes']['variant_option'] ?? false))
            ) {
                continue;
            }

            if (empty($arrField['legend'])) {
                $arrField['legend'] = $arrDCA[$arrField['name']]['attributes']['legend'] ?? '';
            }

            $arrFields[$arrField['name']] = $arrField;
        }

        foreach (array_diff_key($arrDCA, $arrFields) as $strName => $arrField) {

            if (!\is_array($arrField['attributes'] ?? null)
                || ($arrField['attributes']['legend'] ?? '') == ''
                || $this->isExcluded($strName, $blnVariants)
                || ($blnVariants && ($arrField['attributes']['inherit'] ?? false))
                || (!$blnVariants && ($arrField['attributes']['variant_option'] ?? false))
            ) {
                continue;
            }

            $arrFields[$strName] = array(
                'enabled' => \in_array($strName, $arrFixed, true) ? '1' : '',
                'name'    => $strName,
                'legend'  => $arrField['attributes']['legend'],
            );
        }

        return array_values($arrFields);
    }

    /**
     * save_callback to sort attribute wizard fields by legend
     *
     * @param mixed  $varValue The widget value
     * @param object $dc       The DataContainer object
     *
     * @return string
     */
    public function save($varValue, $dc)
    {
        $arrLegends  = array();
        $arrFields   = StringUtil::deserialize($varValue);
        $blnVariants = $this->isVariants($dc->field);

        if (empty($arrFields) || !\is_array($arrFields)) {
            return $varValue;
        }

        if ($blnVariants) {
            $arrFixed = Attribute::getVariantFixedFields($dc->activeRecord->class);
        } else {
            $arrFixed = Attribute::getFixedFields($dc->activeRecord->class);
        }

        foreach ($arrFields as $k => $arrField) {
            if (\in_array($arrField['name'] ?? null, $arrFixed, true)) {
                $arrFields[$k]['enabled'] = '1';
            }

            if (!\in_array($arrField['legend'] ?? null, $arrLegends, true)) {
                $arrLegends[] = $arrField['legend'];
            }
        }

        uksort($arrFields, function ($a, $b) use ($arrFields, $arrLegends) {
            if ($arrFields[$a]['enabled'] && !$arrFields[$b]['enabled']) {
                return -1;
            }

            if ($arrFields[$b]['enabled'] && !$arrFields[$a]['enabled']) {
                return 1;
            }

            if ($arrFields[$a]['legend'] === $arrFields[$b]['legend']) {
                return ($a > $b) ? +1 : -1;
            }

            $posA = array_search($arrFields[$a]['legend'], $arrLegends, true);
            $posB = array_search($arrFields[$b]['legend'], $arrLegends, true);

            return ($posA > $posB) ? +1 : -1;
        });

        $arrValues = array();
        foreach (array_values($arrFields) as $pos => $arrConfig) {
            $arrConfig['position']         = $pos;
            $arrValues[$arrConfig['name']] = $arrConfig;
        }

        return serialize($arrValues);
    }

    /**
     * Returns whether we're currently handling variant attributes.
     *
     * @param string $widgetName
     *
     * @return bool
     */
    private function isVariants($widgetName)
    {
        return 'attributes' !== $widgetName;
    }

    /**
     * Returns whether an attribute is excluded.
     *
     * @param string $attributeName
     * @param bool   $forVariants
     *
     * @return bool
     */
    private function isExcluded($attributeName, $forVariants)
    {
        $excludedFields = $forVariants ? Attribute::getVariantExcludedFields() : Attribute::getExcludedFields();

        return \in_array($attributeName, $excludedFields, true);
    }
}
