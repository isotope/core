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

use Contao\Database;
use Contao\Model\Collection;
use Contao\StringUtil;
use Isotope\Model\Label;

/**
 * Translates labels
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
     * Get a translation of a value using the translation label
     *
     * @param mixed  $varLabel
     * @param string $strLanguage
     *
     * @return mixed
     */
    public static function get($varLabel, $strLanguage = null)
    {
        if (!Database::getInstance()->tableExists(Label::getTable())) {
            return $varLabel;
        }

        if (null === $strLanguage) {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        // Convert Language Tag to Locale ID
        $strLanguage = str_replace('-', '_', $strLanguage);

        // Recursively translate label array
        if (\is_array($varLabel)) {
            foreach ($varLabel as $k => $v) {
                $varLabel[$k] = static::get($v, $strLanguage);
            }

            return $varLabel;
        }

        // Load labels
        static::initialize($strLanguage);

        $varLabel = StringUtil::decodeEntities($varLabel);

        if (isset(static::$arrLabels[$strLanguage][$varLabel])) {
            return static::$arrLabels[$strLanguage][$varLabel];
        }

        return $varLabel;
    }

    /**
     * Add a translation that is not stored in translation table
     *
     * @param string $strLabel       The label
     * @param string $strReplacement The replacement
     * @param string $strLanguage    The language
     */
    public static function add($strLabel, $strReplacement, $strLanguage = null)
    {
        if (null === $strLanguage) {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        static::initialize($strLanguage);

        static::$arrLabels[$strLanguage][StringUtil::decodeEntities($strLabel)] = $strReplacement;
    }

    /**
     * Initialize the data in translation table
     *
     * @param string $strLanguage The language
     */
    protected static function initialize($strLanguage = null)
    {
        if (null === $strLanguage) {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (!isset(static::$arrLoaded[$strLanguage])) {

            /** @var Label[]|Collection $objLabels */
            $objLabels = Label::findBy('language', $strLanguage);

            if (null !== $objLabels) {
                while ($objLabels->next()) {
                    static::$arrLabels[$strLanguage][StringUtil::decodeEntities($objLabels->label)] = $objLabels->replacement;
                }
            }

            static::$arrLoaded[$strLanguage] = true;
        }
    }
}
