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

namespace Isotope\Haste\Input;

class Input extends \Contao\Input
{

    /**
     * Check if auto_item is enabled and return $_GET variable
     *
     * @param string  $strKey            The variable name
     * @param boolean $blnDecodeEntities If true, all entities will be decoded
     * @param boolean $blnKeepUnused     If true, the parameter will not be marked as used (see #4277)
     *
     * @return mixed The cleaned variable value
     */
    public static function getAutoItem($strKey, $blnDecodeEntities=false, $blnKeepUnused=false)
    {
        if (($GLOBALS['TL_CONFIG']['useAutoItem'] ?? false) && isset($GLOBALS['TL_AUTO_ITEM']) && in_array($strKey, $GLOBALS['TL_AUTO_ITEM'])) {
            $strKey = 'auto_item';
        }

        // Fix "unused" bug in Contao Core (see https://github.com/contao/core/issues/7185)
        if (!$blnKeepUnused) {
            unset(static::$arrUnusedGet[$strKey]);
        }

        return static::get($strKey, $blnDecodeEntities, $blnKeepUnused);
    }
}
