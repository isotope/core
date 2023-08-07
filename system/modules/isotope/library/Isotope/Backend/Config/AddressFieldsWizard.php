<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Config;

use Contao\Backend;
use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Isotope\Model\Address;

class AddressFieldsWizard extends Backend
{

    /**
     * For each call, return the name of the next address field in the wizard (for input_field_callback)
     *
     * @param Widget $objWidget
     *
     * @return string
     */
    public function getNextName($objWidget)
    {
        static $arrValues;
        static $i = 0;

        if (empty($arrValues)) {
            System::loadLanguageFile(Address::getTable());
            $arrValues = $objWidget->value;
            $i = 0;
        }

        $arrField = array_shift($arrValues);
        $strName  = $arrField['name'];

        return sprintf(
            '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div style="width:344px">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>',
            $objWidget->name,
            $i,
            $objWidget->name,
            $i++,
            $strName,
            $GLOBALS['TL_DCA'][Address::getTable()]['fields'][$strName]['label'][0] ? : $strName,
            $strName
        );
    }

    /**
     * Generate list of fields and add missing ones from DCA
     *
     * @param mixed $varValue
     *
     * @return array
     */
    public function load($varValue)
    {
        Controller::loadDataContainer(Address::getTable());

        $arrDCA = &$GLOBALS['TL_DCA'][Address::getTable()]['fields'];
        $arrFields = array();
        $arrValues = StringUtil::deserialize($varValue);

        if (!\is_array($arrValues)) {
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

            if (!($arrDCA[$strName]['eval']['feEditable'] ?? false)) {
                continue;
            }

            $mandatory = isset($arrField['eval']['mandatory']) ? $arrField['eval']['mandatory'] : null;

            $arrFields[$strName] = array(
                'name'      => $strName,
                'billing'   => $mandatory === true ? 'mandatory' : ($mandatory === false ? 'enabled' : 'disabled'),
                'shipping'  => $mandatory === true ? 'mandatory' : ($mandatory === false ? 'enabled' : 'disabled'),
            );
        }

        return array_values($arrFields);
    }

    /**
     * save_callback to sort attribute wizard fields by legend
     *
     * @param mixed $varValue
     *
     * @return string
     */
    public function save($varValue)
    {
        Controller::loadDataContainer(Address::getTable());

        $arrFields = StringUtil::deserialize($varValue);

        if (empty($arrFields) || !\is_array($arrFields)) {
            return $varValue;
        }

        $arrValues = array();
        foreach (array_values($arrFields) as $pos => $arrConfig) {
            $arrConfig['position']         = $pos;
            $arrValues[$arrConfig['name']] = $arrConfig;
        }

        return serialize($arrValues);
    }
}
