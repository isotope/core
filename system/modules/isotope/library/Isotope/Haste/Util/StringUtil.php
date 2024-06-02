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

/**
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class StringUtil
{
    /**
     * Text filter options
     */
    const NO_TAGS = 1;
    const NO_BREAKS = 2;
    const NO_EMAILS = 4;
    const NO_INSERTTAGS = 8;
    const NO_ENTITIES = 16;


    /**
     * Recursively replace simple tokens and insert tags
     *
     * @param string $strText
     * @param array  $arrTokens    Array of Tokens
     * @param int    $intTextFlags Filters the tokens and the text for a given set of options
     *
     * @return string
     */
    public static function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags = 0)
    {
        if ($intTextFlags > 0) {
            $arrTokens = static::convertToText($arrTokens, $intTextFlags);
        }

        // Must decode, tokens could be encoded
        $strText = \Contao\StringUtil::decodeEntities($strText);

        // first parse the tokens as they might have if-else clauses
        $strBuffer = \Contao\StringUtil::parseSimpleTokens($strText, $arrTokens);

        // then replace the insert tags
        $strBuffer = Controller::replaceInsertTags($strBuffer, false);

        // check if the insert tags have returned a simple token
        if (strpos($strBuffer, '##') !== false && $strBuffer != $strText) {
            $strBuffer = static::recursiveReplaceTokensAndTags($strBuffer, $arrTokens, $intTextFlags);
        }

        $strBuffer = \Contao\StringUtil::restoreBasicEntities($strBuffer);

        if ($intTextFlags > 0) {
            $strBuffer = static::convertToText($strBuffer, $intTextFlags);
        }

        return $strBuffer;
    }

    /**
     * Convert the given array or string to plain text using given options
     *
     * @param mixed $varValue
     * @param int   $options
     *
     * @return mixed
     */
    public static function convertToText($varValue, $options)
    {
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = static::convertToText($v, $options);
            }

            return $varValue;
        }

        if (!\is_string($varValue)) {
            return $varValue;
        }

        if ($options & static::NO_ENTITIES) {
            $varValue = \Contao\StringUtil::restoreBasicEntities($varValue);
            $varValue = html_entity_decode($varValue);

            // Convert non-breaking to regular white space
            $varValue = str_replace("\xC2\xA0", ' ', $varValue);

            // Remove invisible control characters and unused code points
            $varValue = preg_replace('/[\pC]/u', '', $varValue);
        }

        // Replace friendly email before stripping tags
        if (!($options & static::NO_EMAILS)) {
            $arrEmails = array();
            preg_match_all('{<.+@.+\.[A-Za-z]+>}', $varValue, $arrEmails);

            if (!empty($arrEmails[0])) {
                foreach ($arrEmails[0] as $k => $v) {
                    $varValue = str_replace($v, '%email' . $k . '%', $varValue);
                }
            }
        }

        // Remove HTML tags but keep line breaks for <br> and <p>
        if ($options & static::NO_TAGS) {
            $varValue = strip_tags(preg_replace('{(?!^)<(br|p|/p).*?/?>\n?(?!$)}is', "\n", $varValue));
        }

        if ($options & static::NO_INSERTTAGS) {
            $varValue = strip_insert_tags($varValue);
        }

        // Remove line breaks (e.g. for subject)
        if ($options & static::NO_BREAKS) {
            $varValue = str_replace(array("\r", "\n"), '', $varValue);
        }

        // Restore friendly email after stripping tags
        if (!($options & static::NO_EMAILS) && !empty($arrEmails[0])) {
            foreach ($arrEmails[0] as $k => $v) {
                $varValue = str_replace('%email' . $k . '%', $v, $varValue);
            }
        }

        return $varValue;
    }

    /**
     * Flatten input data, Simple Tokens can't handle arrays
     *
     * @param mixed  $varValue
     * @param string $strKey
     * @param array  $arrData
     * @param string $strPattern
     */
    public static function flatten($varValue, $strKey, array &$arrData, $strPattern = ', ')
    {
        if (is_object($varValue)) {
            return;
        }

        if (!is_array($varValue)) {
            $arrData[$strKey] = $varValue;
            return;
        }

        $blnAssoc = array_is_assoc($varValue);
        $arrValues = array();

        foreach ($varValue as $k => $v) {
            if ($blnAssoc || is_array($v)) {
                static::flatten($v, $strKey.'_'.$k, $arrData);
            } else {
                $arrData[$strKey.'_'.$v] = '1';
                $arrValues[]             = $v;
            }
        }

        $arrData[$strKey] = implode($strPattern, $arrValues);
    }
}
