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
use Contao\Environment;
use Contao\PageModel;

class Url
{

    /**
     * Add a query string to the given URI string or page ID
     *
     * @param string $strQuery
     * @param mixed  $varUrl
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function addQueryString($strQuery, $varUrl = null)
    {
        $strUrl = static::prepareUrl($varUrl);
        $strQuery = trim(\Contao\StringUtil::ampersand($strQuery, false), '&');

        list($strScript, $strQueryString) = explode('?', $strUrl, 2) + array('', '');

        parse_str($strQueryString, $queries);

        $queries = array_filter($queries);
        unset($queries['language']);

        $href = '';

        if (!empty($queries)) {
            parse_str($strQuery, $new);
            $href = '?' . http_build_query(array_merge($queries, $new), '', '&');
        } elseif (!empty($strQuery)) {
            $href = '?' . $strQuery;
        }

        return $strScript . $href;
    }


    /**
     * Prepare URL from ID and keep query string from current string
     *
     * @param string|int|null
     *
     * @return string
     */
    protected static function prepareUrl($varUrl)
    {
        if ($varUrl === null) {
            $varUrl = Environment::get('request');

        } elseif (is_numeric($varUrl)) {
            if (($objJump = PageModel::findByPk($varUrl)) === null) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }

            $varUrl = $objJump->getFrontendUrl();

            $strQueryString = Environment::get('queryString');

            if ($strQueryString != '') {
                $varUrl .= '?' . $strQueryString;
            }
        }

        $varUrl = \Contao\StringUtil::ampersand($varUrl, false);

        return $varUrl;
    }
}
