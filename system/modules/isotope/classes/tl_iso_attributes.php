<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */

namespace Isotope;


/**
 * Class tl_iso_attribuets
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_attributes extends \Backend
{

    /**
     * Disable the internal field name field if it is not empty.
     * @param object
     * @return void
     */
    public function disableFieldName($dc)
    {
        // Hide the field in editAll & overrideAll mode (Thanks to Yanick Witschi)
        if (\Input::get('act') == 'editAll' || \Input::get('act') == 'overrideAll')
        {
            $GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['doNotShow'] = true;
        }
        elseif ($dc->id)
        {
            $objAttribute = \Database::getInstance()->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");

            if ($objAttribute->field_name != '')
            {
                $GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['disabled'] = true;
                $GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['mandatory'] = false;
            }
        }
    }


    /**
     * Hide certain options if this is a variant option
     * @param DataContainer
     */
    public function prepareForVariantOptions($dc)
    {
        $objAttribute = \Database::getInstance()->prepare("SELECT * FROM tl_iso_attributes WHERE id=?")->execute($dc->id);

        if ($objAttribute->variant_option)
        {
            unset($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['options']['eval']['columnFields']['default']);
            unset($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['options']['eval']['columnFields']['group']);
        }
    }


    /**
     * Make sure the system columns are not added as attribute
     * @param mixed
     * @param object
     * @return mixed
     * @throws Exception
     */
    public function validateFieldName($varValue, $dc)
    {
        $this->loadDataContainer('tl_iso_products');

        $varValue = standardize($varValue);

        if (isset($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$varValue]) && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$varValue]['attributes']['systemColumn'])
        {
            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
        }

        return $varValue;
    }


    /**
     * Alter attribtue columns in tl_iso_products table
     * @param object
     * @return void
     */
    public function updateDatabase($dc)
    {
        if (!$dc->activeRecord->fieldName) {
            return;
        }

        // Make sure the latest SQL definitions are written to the DCA
        $GLOBALS['TL_CONFIG']['bypassCache'] = true;
        $this->loadDataContainer('tl_iso_products', true);

        $objUpdater = new \Isotope\DatabaseUpdater();
        $objUpdater->autoUpdateTables(array('tl_iso_products'));
    }


    /**
     * Return an array of select-attributes
     * @param object
     * @return array
     */
    public function getConditionFields($dc)
    {
        $this->loadDataContainer('tl_iso_products');
        $arrFields = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
        {
            if ($arrData['inputType'] == 'select' || ($arrData['inputType'] == 'conditionalselect' && $field != $dc->activeRecord->field_name))
            {
                $arrFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrFields;
    }


    /**
     * Return a list of available rte config files
     * @param object
     * @return array
     */
    public function getRTE($dc)
    {
        $arrOptions = array();

        foreach (scan(TL_ROOT . '/system/config') as $file)
        {
            if (is_file(TL_ROOT . '/system/config/' . $file) && strpos($file, 'tiny') === 0)
            {
                $arrOptions[] = basename($file, '.php');
            }
        }

        return $arrOptions;
    }


    /**
     * Validate table and field of foreignKey
     * @param mixed
     * @param object
     * @return mixed
     */
    public function validateForeignKey($varValue, $dc)
    {
        if ($varValue != '')
        {
            $arrLines = trimsplit('@\r\n|\n|\r@', $varValue);

            foreach ($arrLines as $foreignKey)
            {
                if ($foreignKey == '' || strpos($foreignKey, '#') === 0)
                {
                    continue;
                }

                if (strpos($foreignKey, '=') === 2)
                {
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
     * @param mixed
     * @param object
     * @return mixed
     */
    public function validateDatepicker($varValue, $dc)
    {
        if ($varValue && !in_array($dc->activeRecord->rgxp, array('date', 'time', 'datim')))
        {
            throw new UnexpectedValueException($GLOBALS['TL_LANG']['ERR']['datepickerRgxp']);
        }

        return $varValue;
    }
}
