<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Model\Attribute;
use Isotope\Model\Product;


class AttributeWizard extends \Backend
{

    /**
     * Return list of MultiColumnWizard columns
     * @param   MultiColumnWizard
     * @return  array
     */
    public function getColumns($objWidget)
    {
        $this->loadDataContainer(\Isotope\Model\Product::getTable());

        $arrValues   = $objWidget->value;
        $blnVariants = ($objWidget->name != 'attributes');

        if (!empty($arrValues) && is_array($arrValues)) {
            $arrFixed = $blnVariants ? Attribute::getVariantFixedFields() : Attribute::getFixedFields();

            foreach ($arrValues as $i => $attribute) {

                if (in_array($attribute['name'], $arrFixed)) {
                    $objWidget->addDataToFieldAtIndex($i, 'enabled', array('eval' => array('disabled' => true)));
                }

                $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attribute['name']];
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
     * @param   Widget
     * @param   string
     * @return  string
     */
    public function getNextName($objWidget, $xlabel)
    {
        static $arrValues;
        static $strWidget;
        static $i = 0;

        if ($strWidget != $objWidget->name) {
            $strWidget = $objWidget->name;
            $arrValues = $objWidget->value;
            $i         = 0;
        }

        $arrField = array_shift($arrValues);
        $strName  = $arrField['name'];
        $style = '';

        $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strName];
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
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] ? : $strName,
            $strName
        );
    }

    /**
     * Return list of default and widget legends
     * @param   Widget
     * @return  array
     */
    public function getLegends($objWidget)
    {
        $this->loadDataContainer(\Isotope\Model\Attribute::getTable());
        \System::loadLanguageFile(\Isotope\Model\Product::getTable());

        $arrLegends = $GLOBALS['TL_DCA'][\Isotope\Model\Attribute::getTable()]['fields']['legend']['options'];
        $arrLegends = array_intersect_key($GLOBALS['TL_LANG'][\Isotope\Model\Product::getTable()], array_flip($arrLegends));

        $varValue = $objWidget->value;

        if (!empty($varValue) && is_array($varValue)) {
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
     * @param   mixed
     * @param   DataContainer
     * @return array
     */
    public function load($varValue, $dc)
    {
        $this->loadDataContainer('tl_iso_product');

        $arrDCA      = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $arrFields   = array();
        $arrValues   = deserialize($varValue);
        $blnVariants = ($dc->field != 'attributes');

        if (!is_array($arrValues)) {
            $arrValues = array();
        }

        foreach ($arrValues as $arrField) {

            $strName = $arrField['name'];

            if ($strName == '' || !isset($arrDCA[$strName]) || $arrDCA[$strName]['attributes']['legend'] == '' || ($blnVariants && $arrDCA[$strName]['attributes']['inherit']) || (!$blnVariants && $arrDCA[$strName]['attributes']['variant_option'])) {
                continue;
            }

            if ($arrField['legend'] == '') {
                $arrField['legend'] = $arrDCA[$arrField['name']]['attributes']['legend'];
            }

            $arrFields[$arrField['name']] = $arrField;
        }

        foreach (array_diff_key($arrDCA, $arrFields) as $strName => $arrField) {

            if (!is_array($arrField['attributes']) || $arrField['attributes']['legend'] == '' || ($blnVariants && $arrField['attributes']['inherit']) || (!$blnVariants && $arrField['attributes']['variant_option'])) {
                continue;
            }

            $arrFields[$strName] = array(
                'enabled' => ($arrField['attributes'][($blnVariants ? 'variant_' : '') . 'fixed'] ? '1' : ''),
                'name'    => $strName,
                'legend'  => $arrField['attributes']['legend'],
            );
        }

        return array_values($arrFields);
    }

    /**
     * save_callback to sort attribute wizard fields by legend
     * @param   mixed
     * @param   DataContainer
     * @return  string
     */
    public function save($varValue, $dc)
    {
        $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        $arrLegends  = array();
        $arrFields   = deserialize($varValue);
        $blnVariants = ($dc->field != 'attributes');

        if (empty($arrFields) || !is_array($arrFields)) {
            return $varValue;
        }

        foreach ($arrFields as $k => $arrField) {
            if ($arrDCA[$arrField['name']]['attributes'][($blnVariants ? 'variant_' : '') . 'fixed']) {
                $arrFields[$k]['enabled'] = '1';
            }

            if (!in_array($arrField['legend'], $arrLegends)) {
                $arrLegends[] = $arrField['legend'];
            }
        }

        uksort($arrFields, function ($a, $b) use ($arrFields, $arrLegends) {
            if ($arrFields[$a]['enabled'] && !$arrFields[$b]['enabled']) {
                return -1;
            } elseif ($arrFields[$b]['enabled'] && !$arrFields[$a]['enabled']) {
                return 1;
            } elseif ($arrFields[$a]['legend'] == $arrFields[$b]['legend']) {
                return ($a > $b) ? +1 : -1;
            } else {
                return (array_search($arrFields[$a]['legend'], $arrLegends) > array_search($arrFields[$b]['legend'], $arrLegends)) ? +1 : -1;
            }
        });

        $arrValues = array();
        foreach (array_values($arrFields) as $pos => $arrConfig) {
            $arrConfig['position']         = $pos;
            $arrValues[$arrConfig['name']] = $arrConfig;
        }

        return serialize($arrValues);
    }
}
