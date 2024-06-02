<?php

/**
 * Haste utilities for Contao Open Source CMS
 *
 * Copyright (C) 2012-2013 Codefog & terminal42 gmbh
 *
 * @package    Haste
 * @link       http://github.com/codefog/contao-haste/
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Haste\Util;

use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;

class Format
{

    /**
     * Format date according to the system config
     * @param   int
     * @return  string
     */
    public static function date($intTstamp)
    {
        $strFormat = isset($GLOBALS['objPage']) ? $GLOBALS['objPage']->dateFormat : $GLOBALS['TL_CONFIG']['dateFormat'];

        return System::parseDate($strFormat, $intTstamp);
    }


    /**
     * Format time according to the system config
     * @param   int
     * @return  string
     */
    public static function time($intTstamp)
    {
        $strFormat = isset($GLOBALS['objPage']) ? $GLOBALS['objPage']->timeFormat : $GLOBALS['TL_CONFIG']['timeFormat'];

        return System::parseDate($strFormat, $intTstamp);
    }


    /**
     * Format date & time according to the system config
     * @param   int
     * @return  string
     */
    public static function datim($intTstamp)
    {
        $strFormat = isset($GLOBALS['objPage']) ? $GLOBALS['objPage']->datimFormat : $GLOBALS['TL_CONFIG']['datimFormat'];

        return System::parseDate($strFormat, $intTstamp);
    }

    /**
     * Get field label based on DCA config
     *
     * @param string $strTable
     * @param string $strField
     *
     * @return string
     */
    public static function dcaLabel($strTable, $strField)
    {
        System::loadLanguageFile($strTable);
        Controller::loadDataContainer($strTable);
        $arrField = $GLOBALS['TL_DCA'][$strTable]['fields'][$strField] ?? [];

        // Add the "name" key (backwards compatibility)
        if (!isset($arrField['name'])) {
            $arrField['name'] = $strField;
        }

        return static::dcaLabelFromArray($arrField);
    }

    /**
     * Get field label based on field config
     *
     * @param array $arrField
     *
     * @return string
     */
    public static function dcaLabelFromArray(array $arrField)
    {
        if (!empty($arrField['label'])) {
            $strLabel = is_array($arrField['label']) ? $arrField['label'][0] : $arrField['label'];
        } else {
            $strLabel = is_array($GLOBALS['TL_LANG']['MSC'][$arrField['name']] ?? null) ? $GLOBALS['TL_LANG']['MSC'][$arrField['name']][0] : $GLOBALS['TL_LANG']['MSC'][$arrField['name']] ?? '';
        }

        if (empty($strLabel)) {
            $strLabel = $arrField['name'] ?? '';
        }

        return $strLabel;
    }

    /**
     * Format DCA field value according to Contao Core standard
     *
     * @param string              $strTable
     * @param string              $strField
     * @param mixed               $varValue
     * @param DataContainer|null $objDc
     *
     * @return mixed
     */
    public static function dcaValue($strTable, $strField, $varValue, DataContainer $objDc = null)
    {
        System::loadLanguageFile('default');
        System::loadLanguageFile($strTable);
        Controller::loadDataContainer($strTable);

        if (!isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField])) {
            return '';
        }

        $arrField = $GLOBALS['TL_DCA'][$strTable]['fields'][$strField];

        // Add the "name" key (backwards compatibility)
        if (!isset($arrField['name'])) {
            $arrField['name'] = $strField;
        }

        return static::dcaValueFromArray($arrField, $varValue, $objDc);
    }

    /**
     * Format field value according to Contao Core standard
     *
     * @param array               $arrField
     * @param                     $varValue
     * @param DataContainer|null $objDc
     *
     * @return mixed
     */
    public static function dcaValueFromArray(array $arrField, $varValue, DataContainer $objDc = null)
    {
        $varValue = deserialize($varValue);

        if (is_array($arrField['options_callback'] ?? null) && $objDc !== null) { // Options callback (array)

            $arrCallback = $arrField['options_callback'];
            $arrField['options'] = System::importStatic($arrCallback[0])->{$arrCallback[1]}($objDc);

        } elseif (is_callable($arrField['options_callback'] ?? null) && $objDc !== null) { // Options callback (callable)
            $arrField['options'] = $arrField['options_callback']($objDc);

        } elseif (isset($arrField['foreignKey']) && $varValue) { // foreignKey
            $chunks = explode('.', $arrField['foreignKey'], 2);
            $objOptions = Database::getInstance()->query("SELECT id, " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id IN (" . implode(',', array_map('intval', (array) $varValue)) . ")");
            $arrField['options'] = array();

            while ($objOptions->next()) {
                $arrField['options'][$objOptions->id] = $objOptions->value;
            }
        }

        if (isset($arrField['eval']['csv']) && !is_array($varValue) && str_contains($varValue, $arrField['eval']['csv'])) {
            $varValue = explode($arrField['eval']['csv'], $varValue);
        }

        if (is_array($varValue)) {
            foreach ($varValue as $kk => $vv) {
                $varValue[$kk] = static::dcaValueFromArray($arrField, $vv);
            }

            return implode(', ', $varValue);
        }

        if ('date' === ($arrField['eval']['rgxp'] ?? null)) {
            return static::date($varValue);
        }

        if ('time' === ($arrField['eval']['rgxp'] ?? null)) {
            return static::time($varValue);
        }

        if ('datim' === ($arrField['eval']['rgxp'] ?? null) || in_array(($arrField['flag'] ?? null), array(5, 6, 7, 8, 9, 10)) || 'tstamp' === ($arrField['name'] ?? null)) {
            return static::datim($varValue);
        }

        if (($arrField['eval']['isBoolean'] ?? false) || ('checkbox' === ($arrField['inputType'] ?? null) && !($arrField['eval']['multiple'] ?? false))) {
            return !empty($varValue) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
        }

        if ('textarea' === ($arrField['inputType'] ?? null) && (($arrField['eval']['allowHtml'] ?? false) || ($arrField['eval']['preserveTags'] ?? false))) {
            return \Contao\StringUtil::specialchars($varValue);
        }

        if (is_array($arrField['reference'] ?? null) && isset($arrField['reference'][$varValue])) {
            return is_array($arrField['reference'][$varValue]) ? $arrField['reference'][$varValue][0] : $arrField['reference'][$varValue];
        }

        if ((($arrField['eval']['isAssociative'] ?? null) || array_is_assoc($arrField['options'] ?? null)) && isset($arrField['options'][$varValue])) {
            return is_array($arrField['options'][$varValue]) ? $arrField['options'][$varValue][0] : $arrField['options'][$varValue];
        }

        return $varValue;
    }
}
