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

use Isotope\Model\Config;
use Isotope\Model\RequestCache;
use Isotope\Model\TaxClass;
use Isotope\Model\ProductCollection\Cart;
use Haste\Haste;


/**
 * Class Isotope
 *
 * The base class for all Isotope components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 */
class Isotope extends \Controller
{

    /**
     * True if the system has been initialized
     * @var bool
     */
    protected static $blnInitialized = false;

    /**
     * Current cart instance
     * @var Isotope\Model\ProductCollection\Cart
     */
    protected static $objCart;

    /**
     * Current config instance
     * @var Isotope\Model\Config
     */
    protected static $objConfig;

    /**
     * Current request cache instance
     * @var Isotope\Model\RequestCache
     */
    protected static $objRequestCache;


    public static function initialize()
    {
        if (static::$blnInitialized === false) {

            static::$blnInitialized = true;

            // Make sure field data is available
            Haste::getInstance()->call('loadDataContainer', 'tl_iso_product');
            \System::loadLanguageFile('tl_iso_product');

            // Initialize request cache for product list filters
            if (\Input::get('isorc') != '') {

                if (static::getRequestCache()->isEmpty()) {
                    global $objPage;
                    $objPage->noSearch = 1;

                } elseif (static::getRequestCache()->id != \Input::get('isorc')) {

                    unset($_GET['isorc']);

                    // Unset the language parameter
                    if ($GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                        unset($_GET['language']);
                    }

                    $strQuery = http_build_query($_GET);
                    \System::redirect(preg_replace('/\?.*$/i', '', \Environment::get('request')) . (($strQuery) ? '?' . $strQuery : ''));
                }
            }
        }
    }


    /**
     * Get the currently active Isotope cart
     * @return Isotope\Model\ProductCollection\Cart|null
     */
    public static function getCart()
    {
        if (null === static::$objCart && TL_MODE == 'FE') {
            static::initialize();
            if ((static::$objCart = Cart::findForCurrentStore()) !== null) {
                static::$objCart->mergeGuestCart();
            }
        }

        return static::$objCart;
    }


    /**
     * Set the currently active Isotope cart
     * @param Isotope\Model\ProductCollection\Cart
     */
    public static function setCart(Cart $objCart)
    {
        static::$objCart = $objCart;
    }


    /**
     * Get the currently active Isotope configuration
     * @return Isotope\Model\Config
     */
    public static function getConfig()
    {
        if (null === static::$objConfig) {
            static::initialize();

            if (($objCart = static::getCart()) !== null) {
                static::$objConfig = Config::findByPk($objCart->config_id);
            }

            // If cart was null or still did not find a config
            if (null === static::$objConfig) {
                global $objPage;

                static::$objConfig = (TL_MODE == 'FE' ? Config::findByRootPageOrFallback($objPage->rootId) : Config::findByFallback());
            }
        }

        return static::$objConfig;
    }


    /**
     * Set the currently active Isotope configuration
     * @param Isotope\Model\Config|null
     */
    public static function setConfig(Config $objConfig=null)
    {
        static::$objConfig = $objConfig;
    }

    /**
     * Get active request cache
     * @return  RequestCache
     */
    public static function getRequestCache()
    {
        if (null === static::$objRequestCache) {
            static::$objRequestCache = RequestCache::findByIdAndStore(\Input::get('isorc'), static::getCart()->store_id);

            if (null === static::$objRequestCache) {
                static::$objRequestCache = new RequestCache();
            }
        }

        return static::$objRequestCache;
    }


    /**
     * Calculate price trough hook and foreign prices
     * @param float
     * @param object
     * @param string
     * @param integer
     * @return float
     */
    public static function calculatePrice($fltPrice, $objSource, $strField, $intTaxClass=0)
    {
        if (!is_numeric($fltPrice))
        {
            return $fltPrice;
        }

        // !HOOK: calculate price
        if (isset($GLOBALS['ISO_HOOKS']['calculatePrice']) && is_array($GLOBALS['ISO_HOOKS']['calculatePrice']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['calculatePrice'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $fltPrice = $objCallback->$callback[1]($fltPrice, $objSource, $strField, $intTaxClass);
            }
        }

        $objConfig = static::getConfig();

        if ($objConfig->priceMultiplier != 1)
        {
            switch ($objConfig->priceCalculateMode)
            {
                case 'mul':
                    $fltPrice = $fltPrice * $objConfig->priceCalculateFactor;
                    break;

                case 'div':
                    $fltPrice = $fltPrice / $objConfig->priceCalculateFactor;
                    break;
            }
        }

        // Possibly add/subtract tax
        if (($objTaxClass = TaxClass::findByPk($intTaxClass)) !== null)
        {
            $fltPrice = $objTaxClass->calculatePrice($fltPrice);
        }

        return static::roundPrice($fltPrice);
    }


    /**
     * Rounds a price according to store config settings
     * @param float original value
     * @param bool apply rounding increment
     * @return float rounded value
     */
    public static function roundPrice($fltValue, $blnApplyRoundingIncrement=true)
    {
        $objConfig = static::getConfig();

        if ($blnApplyRoundingIncrement && $objConfig->priceRoundIncrement == '0.05')
        {
            $fltValue = (round(20 * $fltValue)) / 20;
        }

        return round($fltValue, $objConfig->priceRoundPrecision);
    }


    /**
     * Format given price according to store config settings
     * @param float
     * @return float
     */
    public static function formatPrice($fltPrice)
    {
        // If price or override price is a string
        if (!is_numeric($fltPrice))
        {
            return $fltPrice;
        }

        $arrFormat = $GLOBALS['ISO_NUM'][static::getConfig()->currencyFormat];

        if (!is_array($arrFormat))
        {
            return $fltPrice;
        }

        return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
    }


    /**
     * Format given price according to store config settings, including currency representation
     * @param float
     * @param boolean
     * @param string
     * @return string
     */
    public static function formatPriceWithCurrency($fltPrice, $blnHtml=true, $strCurrencyCode=null)
    {
        // If price or override price is a string
        if (!is_numeric($fltPrice))
        {
            return $fltPrice;
        }

        $objConfig = static::getConfig();
        $strCurrency = ($strCurrencyCode != '' ? $strCurrencyCode : $objConfig->currency);
        $strPrice = static::formatPrice($fltPrice);

        if ($objConfig->currencySymbol && $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency] != '')
        {
            $strCurrency = (($objConfig->currencyPosition == 'right' && $objConfig->currencySpace) ? ' ' : '') . ($blnHtml ? '<span class="currency">' : '') . $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency] . ($blnHtml ? '</span>' : '') . (($objConfig->currencyPosition == 'left' && $objConfig->currencySpace) ? ' ' : '');
        }
        else
        {
            $strCurrency = ($objConfig->currencyPosition == 'right' ? ' ' : '') . ($blnHtml ? '<span class="currency">' : '') . $strCurrency . ($blnHtml ? '</span>' : '') . ($objConfig->currencyPosition == 'left' ? ' ' : '');
        }

        if ($objConfig->currencyPosition == 'right')
        {
            return $strPrice . $strCurrency;
        }

        return $strCurrency . $strPrice;
    }


    /**
     * Format the number of items and return the items string
     * @param integer
     * @return string
     */
    public static function formatItemsString($intItems)
    {
        if ($intItems == 1)
        {
            return $GLOBALS['TL_LANG']['ISO']['productSingle'];
        }
        else
        {
            $arrFormat = $GLOBALS['ISO_NUM'][static::getConfig()->currencyFormat];

            if (is_array($arrFormat))
            {
                $intItems = number_format($intItems, 0, $arrFormat[1], $arrFormat[2]);
            }

            return sprintf($GLOBALS['TL_LANG']['ISO']['productMultiple'], $intItems);
        }
    }


    /**
     * Callback for isoButton Hook
     * @param array
     * @return array
     */
    public static function defaultButtons($arrButtons)
    {
        $arrButtons['update'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']);
        $arrButtons['add_to_cart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('\Isotope\Frontend', 'addToCart'));

        return $arrButtons;
    }


    /**
     * Validate a custom regular expression
     * @param string
     * @param mixed
     * @param object
     * @return boolean
     */
    public static function validateRegexp($strRegexp, $varValue, \Widget $objWidget)
    {
        switch ($strRegexp)
        {
            case 'price':
                if (!preg_match('/^[\d \.-]*$/', $varValue))
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['digit'], $objWidget->label));
                }

                return true;
                break;

            case 'discount':
                if (!preg_match('/^[-+]\d+(\.\d{1,2})?%?$/', $varValue))
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['discount'], $objWidget->label));
                }

                return true;
                break;

            case 'surcharge':
                if (!preg_match('/^-?\d+(\.\d{1,2})?%?$/', $varValue))
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['surcharge'], $objWidget->label));
                }

                return true;
                break;
        }

        return false;
    }


    /**
     * Format value (based on DC_Table::show(), Contao 2.9.0)
     * @param string
     * @param string
     * @param mixed
     * @return string
     */
    public static function formatValue($strTable, $strField, $varValue)
    {
        $varValue = deserialize($varValue);

        if (!is_array($GLOBALS['TL_DCA'][$strTable]))
        {
            Haste::getInstance()->call('loadDataContainer', $strTable);
            \System::loadLanguageFile($strTable);
        }

        // Get field value
        if (strlen($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['foreignKey']))
        {
            $chunks = explode('.', $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['foreignKey']);
            $varValue = empty($varValue) ? array(0) : $varValue;
            $objKey = \Database::getInstance()->execute("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id IN (" . implode(',', array_map('intval', (array) $varValue)) . ")");

            return implode(', ', $objKey->fetchEach('value'));
        }

        elseif (is_array($varValue))
        {
            foreach ($varValue as $kk => $vv)
            {
                $varValue[$kk] = static::formatValue($strTable, $strField, $vv);
            }

            return implode(', ', $varValue);
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'date')
        {
            return static::formatDate($varValue);
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'time')
        {
            return static::formatTime($varValue);
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'datim' || in_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['flag'], array(5, 6, 7, 8, 9, 10)) || $strField == 'tstamp')
        {
            return static::formatDatim($varValue);
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['multiple'])
        {
            return strlen($varValue) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'] == 'textarea' && ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['allowHtml'] || $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['preserveTags']))
        {
            return specialchars($varValue);
        }

        elseif (is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference']))
        {
            return isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue]) ? ((is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue])) ? $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue][0] : $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue]) : $varValue;
        }

        return $varValue;
    }


    /**
     * Format label (based on DC_Table::show(), Contao 2.9.0)
     * @param string
     * @param string
     * @return string
     */
    public static function formatLabel($strTable, $strField)
    {
        if (!is_array($GLOBALS['TL_DCA'][$strTable]))
        {
            Haste::getInstance()->call('loadDataContainer', $strTable);
            \System::loadLanguageFile($strTable);
        }

        // Label
        if (!empty($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label']))
        {
            $strLabel = is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label']) ? $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'][0] : $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'];
        }

        else
        {
            $strLabel = is_array($GLOBALS['TL_LANG']['MSC'][$strField]) ? $GLOBALS['TL_LANG']['MSC'][$strField][0] : $GLOBALS['TL_LANG']['MSC'][$strField];
        }

        if ($strLabel == '')
        {
            $strLabel = $strField;
        }

        return $strLabel;
    }


    /**
     * Format options label and value
     * @param   array
     * @param   string
     */
    public static function formatOptions(array $arrData, $strTable='tl_iso_product', $blnSkipEmpty=true)
    {
        $arrOptions = array();

        foreach ($arrData as $field => $value)
        {
            if ($blnSkipEmpty && ($value == '' || $value == '-'))
                continue;

            $arrOptions[$field] = array
            (
                'label'    => static::formatLabel($strTable, $field),
                'value'    => Haste::getInstance()->call('replaceInsertTags', static::formatValue($strTable, $field, $value)),
            );
        }

        return $arrOptions;
    }
}
