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

namespace Isotope;

/**
 * Translates labels
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class Translation
{
    /**
     * Labels
     * @var array
     */
    protected static $arrLabels = array();

    /**
     * Labels loaded
     * @var array
     */
    protected static $arrLoaded = array();

    /**
     * Get a translation of a value using the tl_iso_label table
     * @param   mixed
     * @param   boolean
     * @return  mixed
     */
    public static function get($varLabel, $strLanguage=null)
    {
        // Recursively translate label array
        if (is_array($varLabel)) {
            foreach ($varLabel as $k => $v) {
                $varLabel[$k] = static::get($v, $strLanguage);
            }

            return $varLabel;
        }

        // Load labels
        static::initialize($strLanguage);

        if (isset(static::$arrLabels[$strLanguage][$varLabel])) {

            static::$arrLabels[$strLanguage][$varLabel] = \String::decodeEntities(static::$arrLabels[$strLanguage][$varLabel]);

            return static::$arrLabels[$strLanguage][$varLabel];
        }

        return $varLabel;
    }

    /**
     * Add a translation that is not stored in tl_iso_label
     * @param   string The label
     * @param   string The replacement
     * @param   string The language
     */
    public static function add($strLabel, $strReplacement, $strLanguage=null)
    {
        if ($strLanguage === null) {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        static::initialize($strLanguage);

        static::$arrLabels[$strLanguage][$strLabel] = $strReplacement;
    }


    /**
     * Initialize the data in tl_iso_label
     * @param string The language
     */
    protected static function initialize($strLanguage=null)
    {
        if ($strLanguage === null) {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (!isset(static::$arrLoaded[$strLanguage])) {

            $objLabels = \Database::getInstance()->prepare('SELECT * FROM tl_iso_labels WHERE language=?')->execute($strLanguage);
            while ($objLabels->next()) {
                static::$arrLabels[$objLabels->label] = $objLabels->replacement;
            }

            static::$arrLoaded[$strLanguage] = true;
        }
    }
}