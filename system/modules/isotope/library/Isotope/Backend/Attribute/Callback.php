<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Attribute;

use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;
use Isotope\DatabaseUpdater;

class Callback extends \Backend
{

    /**
     * Disable the internal field name field if it is not empty.
     *
     * @param object $dc
     */
    public function disableFieldName($dc)
    {
        $act = \Input::get('act');

        // Hide the field in editAll & overrideAll mode (Thanks to Yanick Witschi)
        if ('editAll' === $act || 'overrideAll' === $act) {
            $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['field_name']['eval']['doNotShow'] = true;
        } elseif ($dc->id) {
            $objAttribute = \Database::getInstance()->execute("SELECT * FROM tl_iso_attribute WHERE id={$dc->id}");

            if ($objAttribute->field_name != '') {
                $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['field_name']['eval']['disabled']  = true;
                $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['field_name']['eval']['mandatory'] = false;
            }
        }
    }

    /**
     * Show price column in dcaWizard if attribute is not a variant option
     *
     * @param \Widget|object $objWidget
     *
     * @return string
     */
    public function initializeTableOptions(\Widget $objWidget)
    {
        /** @var Attribute $objAttribute */

        if ('iso_products' === \Input::get('do')) {
            $objAttribute = Attribute::findByFieldName($objWidget->name);
        } else {
            $objAttribute = Attribute::findByPk(\Input::get('id'));
        }

        if (null !== $objAttribute && !$objAttribute->isVariantOption()) {
            $objWidget->fields = array_merge($objWidget->fields, array('price'));
        }

        return AttributeOption::getTable();
    }

    /**
     * Make sure the system columns are not added as attribute
     *
     * @param mixed $varValue
     *
     * @return mixed
     * @throws \Exception
     */
    public function validateFieldName($varValue)
    {
        \Controller::loadDataContainer('tl_iso_product');

        $varValue = str_replace('-', '_', standardize($varValue));

        if (isset($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$varValue])
            && $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$varValue]['attributes']['systemColumn']
        ) {
            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
        }

        return $varValue;
    }

    /**
     * Alter attribute columns in tl_iso_product table
     *
     * @param object $dc
     */
    public function updateDatabase($dc)
    {
        if (!$dc->activeRecord->field_name) {
            return;
        }

        // Make sure the latest SQL definitions are written to the DCA
        $GLOBALS['TL_CONFIG']['bypassCache'] = true;
        \Controller::loadDataContainer('tl_iso_product', true);

        $objUpdater = new DatabaseUpdater();
        $objUpdater->autoUpdateTables(array('tl_iso_product'));
    }

    /**
     * Return an array of select-attributes
     * @param object
     * @return array
     */
    public function getConditionFields($dc)
    {
        \Controller::loadDataContainer('tl_iso_product');
        $arrFields = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ('select' === $arrData['inputType']
                || ('conditionalselect' === $arrData['inputType'] && $field != $dc->activeRecord->field_name)
            ) {
                $arrFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrFields;
    }

    /**
     * Return a list of available rte config files
     *
     * @return array
     */
    public function getRTE()
    {
        $options = array();

        if (version_compare(VERSION, '4.0', '<')) {
            foreach (scan(TL_ROOT . '/system/config') as $file) {
                if (is_file(TL_ROOT . '/system/config/' . $file) && strpos($file, 'tiny') === 0) {
                    $options[] = basename($file, '.php');
                }
            }
        } else {
            foreach (preg_grep('/^be_tiny/', array_keys(\TemplateLoader::getFiles())) as $template) {
                $options[] = substr($template, 3);
            }
        }

        return $options;
    }

    /**
     * Validate table and field of foreignKey
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function validateForeignKey($varValue)
    {
        if ($varValue != '') {
            $arrLines = trimsplit('@\r\n|\n|\r@', $varValue);

            foreach ($arrLines as $foreignKey) {
                if ($foreignKey == '' || strpos($foreignKey, '#') === 0) {
                    continue;
                }

                if (strpos($foreignKey, '=') === 2) {
                    $foreignKey = substr($foreignKey, 3);
                }

                list($strTable, $strField) = explode('.', $foreignKey, 2);
                \Database::getInstance()->execute("SELECT $strField FROM $strTable");
            }
        }

        return $varValue;
    }

    /**
     * To enable date picker, the rgxp must be date, time or datim
     *
     * @param mixed  $varValue
     * @param object $dc
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException if rgxp is not valid for a datepicker
     */
    public function validateDatepicker($varValue, $dc)
    {
        if ($varValue && !in_array($dc->activeRecord->rgxp, ['date', 'time', 'datim'], true)) {
            throw new \UnexpectedValueException($GLOBALS['TL_LANG']['ERR']['datepickerRgxp']);
        }

        return $varValue;
    }
}
