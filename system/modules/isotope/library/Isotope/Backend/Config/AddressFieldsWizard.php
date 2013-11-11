<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Backend\Config;


class AddressFieldsWizard extends \Backend
{

	/**
     * For each call, return the name of the next address field in the wizard (for input_field_callback)
     * @param   Widget
     * @param   string
     * @return  string
     */
    public function getNextName($objWidget, $xlabel)
    {
        static $arrValues;
        static $i = 0;

        if (null === $arrValues) {
            \System::loadLanguageFile(\Isotope\Model\Address::getTable());
            $arrValues = $objWidget->value;
            $i = 0;
        }

        $arrField = array_shift($arrValues);
        $strName = $arrField['name'];

        return sprintf(
            '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div style="width:344px">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>',
            $objWidget->name,
            $i,
            $objWidget->name,
            $i++,
            $strName,
            $GLOBALS['TL_DCA'][\Isotope\Model\Address::getTable()]['fields'][$strName]['label'][0] ?: $strName,
            $strName
        );
    }

    /**
     * Generate list of fields and add missing ones from DCA
     * @param   mixed
     * @param   DataContainer
     * @return array
     */
    public function load($varValue, $dc)
    {
        $this->loadDataContainer(\Isotope\Model\Address::getTable());

        $arrDCA = &$GLOBALS['TL_DCA'][\Isotope\Model\Address::getTable()]['fields'];
        $arrFields = array();
        $arrValues = deserialize($varValue);

        if (!is_array($arrValues)) {
            $arrValues = array();
        }

        foreach ($arrValues as $arrField) {

            $strName = $arrField['name'];

            if ($strName == '' || !isset($arrDCA[$strName]) || !$arrDCA[$strName]['eval']['feEditable']) {
                continue;
            }

            $arrFields[$arrField['name']] = $arrField;
        }

        foreach (array_diff_key($arrDCA, $arrFields) as $strName => $arrField) {

            if (!$arrDCA[$strName]['eval']['feEditable']) {
                continue;
            }

            $arrFields[$strName] = array(
                'name'      => $strName,
                'billing'   => ($arrField['eval']['mandatory'] === true ? 'mandatory' : ($arrField['eval']['mandatory'] === false ? 'enabled' : 'disabled')),
                'shipping'  => ($arrField['eval']['mandatory'] === true ? 'mandatory' : ($arrField['eval']['mandatory'] === false ? 'enabled' : 'disabled')),
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
        $this->loadDataContainer(\Isotope\Model\Address::getTable());

        $arrFields = deserialize($varValue);

        if (empty($arrFields) || !is_array($arrFields)) {
            return $varValue;
        }

        $arrValues = array();
        foreach (array_values($arrFields) as $pos => $arrConfig) {
            $arrConfig['position'] = $pos;
            $arrValues[$arrConfig['name']] = $arrConfig;
        }

        return serialize($arrValues);
    }
}
