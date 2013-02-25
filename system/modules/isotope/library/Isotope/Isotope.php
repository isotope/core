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
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate;
use Isotope\Model\ProductCollection\Cart;


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
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance;

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
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone() {}


    /**
     * Prevent direct instantiation (Singleton)
     */
    protected function __construct()
    {
        parent::__construct();

        $this->import('Database');
        $this->import('FrontendUser', 'User');
    }


    /**
     * Allow access to all protected parent methods
     */
    public function call($name, $arguments=null)
    {
        $arguments = $arguments === null ? array() : (is_array($arguments) ? $arguments : array($arguments));

        return call_user_func_array(array($this, $name), $arguments);
    }


    /**
     * Instantiate the Isotope object
     * @return object
     */
    public static function getInstance()
    {
        if (null === static::$objInstance) {
            static::initialize();
            static::$objInstance = new static();
        }

        return static::$objInstance;
    }


    public static function initialize()
    {
        if (static::$blnInitialized === false) {

            static::$blnInitialized = true;

            // Make sure field data is available
            static::getInstance()->call('loadDataContainer', 'tl_iso_products');
            \System::loadLanguageFile('tl_iso_products');

            if (TL_MODE == 'FE' && strpos(\Environment::get('script'), 'postsale.php') === false && strpos(\Environment::get('script'), 'cron.php') === false) {

                // Initialize request cache for product list filters
                if (\Input::get('isorc') != '') {

                    $objRequestCache = \Database::getInstance()->prepare("SELECT * FROM tl_iso_requestcache WHERE id=? AND store_id=?")->execute(\Input::get('isorc'), static::getConfig()->store_id);

                    if ($objRequestCache->numRows) {

                        $GLOBALS['ISO_FILTERS'] = deserialize($objRequestCache->filters);
                        $GLOBALS['ISO_SORTING'] = deserialize($objRequestCache->sorting);
                        $GLOBALS['ISO_LIMIT'] = deserialize($objRequestCache->limits);

                        global $objPage;
                        $objPage->noSearch = 1;

                    } else {

                        unset($_GET['isorc']);

                        // Unset the language parameter
                        if ($GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                            unset($_GET['language']);
                        }

                        $strQuery = http_build_query($_GET);
                        \System::redirect(preg_replace('/\?.*$/i', '', \Environment::get('request')) . (($strQuery) ? '?' . $strQuery : ''));
                    }
                }

                // Set the product from the auto_item parameter
                if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
                    \Input::setGet('product', \Input::get('auto_item'));
                }
            }
        }
    }


    /**
     * Get the currently active Isotope cart
     * @return Isotope\Model\ProductCollection\Cart
     */
    public static function getCart()
    {
        if (null === static::$objCart) {
            static::initialize();
            static::$objCart = Cart::getDefaultForStore((int) static::getConfig()->id, (int) static::getConfig()->store_id);
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

            if ($_SESSION['ISOTOPE']['config_id'] > 0) {
                static::overrideConfig($_SESSION['ISOTOPE']['config_id']);
            } else {
                static::resetConfig();
            }
        }

        return static::$objConfig;
    }


    /**
     * Set the currently active Isotope configuration
     * @param Isotope\Model\Config
     */
    public static function setConfig(Config $objConfig)
    {
        static::$objConfig = $objConfig;
    }


    /**
     * Set the default store config
     */
    public static function resetConfig()
    {
        if (\Database::getInstance()->tableExists('tl_iso_config'))
        {
            if (TL_MODE == 'FE')
            {
                global $objPage;

                static::$objConfig = Config::findByRootPageOrFallback($objPage->rootId);
            }
            else
            {
                static::$objConfig = Config::findByFallback();
            }
        }

        if (null === static::$objConfig)
        {
            // Display error message in Isotope related backend modules
            if (TL_MODE == 'BE')
            {
                $do = \Input::get('do');

                if (isset($GLOBALS['BE_MOD']['isotope'][$do]))
                {
                    $_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'];

                    if ($do == 'iso_products')
                    {
                        \System::redirect('contao/main.php?do=iso_setup&mod=configs&table=tl_iso_config&act=create');
                    }
                }
            }
            else
            {
                trigger_error($GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'], E_USER_WARNING);
            }

            return;
        }
    }


    /**
     * Manual override of the store configuration
     * @param integer
     */
    public static function overrideConfig($intConfig)
    {
        if ((null === static::$objConfig || static::$objConfig->id != $intConfig) && (static::$objConfig = Config::findByPk($intConfig)) === null)
        {
            static::resetConfig();
        }
    }


    /**
     * Calculate price trough hook and foreign prices
     * @param float
     * @param object
     * @param string
     * @param integer
     * @return float
     */
    public static function calculatePrice($fltPrice, &$objSource, $strField, $intTaxClass=0)
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
     * Calculate a product surcharge and apply taxes if necessary
     * @param string
     * @param string
     * @param integer
     * @param array
     * @param object
     */
    public static function calculateSurcharge($strPrice, $strLabel, $intTaxClass, $arrProducts, $objSource)
    {
        $blnPercentage = substr($strPrice, -1) == '%' ? true : false;

        if ($blnPercentage)
        {
            $fltTotal = 0;

            foreach( $arrProducts as $objProduct )
            {
                $fltTotal += (float) $objProduct->total_price;
            }

            $fltSurcharge = (float) substr($strPrice, 0, -1);
            $fltPrice = static::roundPrice($fltTotal / 100 * $fltSurcharge);
        }
        else
        {
            $fltPrice = static::calculatePrice((float) $strPrice, $objSource, 'price', $intTaxClass);
        }

        $arrSurcharge = array
        (
            'label'         => $strLabel,
            'price'         => ($blnPercentage ? $strPrice : '&nbsp;'),
            'total_price'   => $fltPrice,
            'tax_class'     => $intTaxClass,
            'before_tax'    => ($intTaxClass ? true : false),
        );

        if ($intTaxClass == -1)
        {
            $fltTotal = 0;

            foreach ($arrProducts as $objProduct)
            {
                $fltTotal += (float) $objProduct->tax_free_total_price;
            }

            $arrSubtract = array();
            foreach ($arrProducts as $objProduct)
            {
                if ($blnPercentage)
                {
                    $fltProductPrice = $objProduct->total_price / 100 * $fltSurcharge;
                }
                else
                {
                    $fltProductPrice = $fltPrice / 100 * (100 / $fltTotal * $objProduct->tax_free_total_price);
                }

                $fltProductPrice = $fltProductPrice > 0 ? (floor($fltProductPrice * 100) / 100) : (ceil($fltProductPrice * 100) / 100);
                $arrSubtract[$objProduct->collection_id] = $fltProductPrice;
            }

            $arrSurcharge['tax_class'] = 0;
            $arrSurcharge['before_tax'] = true;
            $arrSurcharge['products'] = $arrSubtract;
        }

        return $arrSurcharge;
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
     * Send an email using the isotope e-mail templates
     * @param integer
     * @param string
     * @param string
     * @param array
     * @param string
     * @param object
     */
    public static function sendMail($intId, $strRecipient, $strLanguage, $arrData, $strReplyTo='', $objCollection=null)
    {
        try
        {
            $objEmail = new \Isotope\Email($intId, $strLanguage, $objCollection);

            if ($strReplyTo != '')
            {
                $objEmail->replyTo($strReplyTo);
            }

            $objEmail->send($strRecipient, $arrData);
        }
        catch (Exception $e)
        {
            \System::log('Isotope email error: ' . $e->getMessage(), __METHOD__, TL_ERROR);
        }
    }


    /**
     * Update ConditionalSelect to include the product ID in conditionField
     * @param string
     * @param array
     * @param object
     * @return array
     */
    public static function mergeConditionalOptionData($strField, $arrData, &$objProduct=null)
    {
        $arrData['eval']['conditionField'] = $arrData['attributes']['conditionField'] . (is_object($objProduct) ? '_' . $objProduct->formSubmit : '');

        return $arrData;
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
     * Standardize and calculate the total of multiple weights
     *
     * It's probably faster in theory to convert only the total to the final unit, and not each product weight.
     * However, we might loose precision, not sure about that.
     * Based on formulas found at http://jumk.de/calc/gewicht.shtml
     * @param array
     * @param string
     * @return mixed
     */
    public static function calculateWeight($arrWeights, $strUnit)
    {
        if (!is_array($arrWeights) || empty($arrWeights))
        {
            return 0;
        }

        $fltWeight = 0;

        foreach ($arrWeights as $weight)
        {
            if (is_array($weight) && $weight['value'] > 0 && $weight['unit'] != '')
            {
                $fltWeight += static::convertWeight(floatval($weight['value']), $weight['unit'], 'kg');
            }
        }

        return static::convertWeight($fltWeight, 'kg', $strUnit);
    }


    /**
     * Convert weight units
     * Supported source/target units: mg, g, kg, t, ct, oz, lb, st, grain
     * @param float
     * @param string
     * @param string
     * @return mixed
     * @throws Exception
     */
    public static function convertWeight($fltWeight, $strSourceUnit, $strTargetUnit)
    {
        switch ($strSourceUnit)
        {
            case 'mg':
                return static::convertWeight(($fltWeight / 1000000), 'kg', $strTargetUnit);

            case 'g':
                return static::convertWeight(($fltWeight / 1000), 'kg', $strTargetUnit);

            case 'kg':
                switch ($strTargetUnit)
                {
                    case 'mg':
                        return $fltWeight * 1000000;

                    case 'g':
                        return $fltWeight * 1000;

                    case 'kg':
                        return $fltWeight;

                    case 't':
                        return $fltWeight / 1000;

                    case 'ct':
                        return $fltWeight * 5000;

                    case 'oz':
                        return $fltWeight / 28.35 * 1000;

                    case 'lb':
                        return $fltWeight / 0.45359243;

                    case 'st':
                        return $fltWeight / 6.35029318;

                    case 'grain':
                        return $fltWeight / 64.79891 * 1000000;

                    default:
                        throw new InvalidArgumentException('Unknown target weight unit "' . $strTargetUnit . '"');
                }

            case 't':
                return static::convertWeight(($fltWeight * 1000), 'kg', $strTargetUnit);

            case 'ct':
                return static::convertWeight(($fltWeight / 5000), 'kg', $strTargetUnit);

            case 'oz':
                return static::convertWeight(($fltWeight * 28.35 / 1000), 'kg', $strTargetUnit);

            case 'lb':
                return static::convertWeight(($fltWeight * 0.45359243), 'kg', $strTargetUnit);

            case 'st':
                return static::convertWeight(($fltWeight * 6.35029318), 'kg', $strTargetUnit);

            case 'grain':
                return static::convertWeight(($fltWeight * 64.79891 / 1000000), 'kg', $strTargetUnit);

            default:
                throw new InvalidArgumentException('Unknown source weight unit "' . $strSourceUnit . '"');
        }
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
     * Translate a value using the tl_iso_label table
     * @param mixed
     * @param boolean
     * @return mixed
     */
    public static function translate($label, $language=false)
    {
        if (!in_array('isotope_multilingual', \Config::getInstance()->getActiveModules()))
        {
            return $label;
        }

        // Recursively translate label array
        if (is_array($label))
        {
            foreach ($label as $k => $v)
            {
                $label[$k] = static::translate($v, $language);
            }

            return $label;
        }

        if (!$language)
        {
            $language = $GLOBALS['TL_LANGUAGE'];
        }

        if (!is_array($GLOBALS['TL_LANG']['TBL'][$language]))
        {
            $GLOBALS['TL_LANG']['TBL'][$language] = array();
            $objLabels = \Database::getInstance()->prepare("SELECT * FROM tl_iso_labels WHERE language=?")->execute($language);

            while ($objLabels->next())
            {
                $GLOBALS['TL_LANG']['TBL'][$language][\String::decodeEntities($objLabels->label)] = $objLabels->replacement;
            }
        }

        $label = \String::decodeEntities($label);

        return $GLOBALS['TL_LANG']['TBL'][$language][$label] ? $GLOBALS['TL_LANG']['TBL'][$language][$label] : $label;
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
            Isotope::getInstance()->call('loadDataContainer', $strTable);
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
            return \System::parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $varValue);
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'time')
        {
            return \System::parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $varValue);
        }

        elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'datim' || in_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['flag'], array(5, 6, 7, 8, 9, 10)) || $strField == 'tstamp')
        {
            return \System::parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $varValue);
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
            Isotope::getInstance()->call('loadDataContainer', $strTable);
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
     * Merge media manager data from fallback and translated product data
     * @param array
     * @param array
     * @return array
     */
    public static function mergeMediaData($arrCurrent, $arrParent)
    {
        $arrTranslate = array();

        if (is_array($arrParent) && !empty($arrParent))
        {
            // Create an array of images where key = image name
            foreach( $arrParent as $i => $image)
            {
                if ($image['translate'] != 'all')
                {
                    $arrTranslate[$image['src']] = $image;
                }
            }
        }

        if (is_array($arrCurrent) && !empty($arrCurrent))
        {
            foreach ($arrCurrent as $i => $image)
            {
                if (isset($arrTranslate[$image['src']]))
                {
                    if ($arrTranslate[$image['src']]['translate'] == '')
                    {
                        $arrCurrent[$i] = $arrTranslate[$image['src']];
                    }
                    else
                    {
                        $arrCurrent[$i]['link'] = $arrTranslate[$image['src']]['link'];
                        $arrCurrent[$i]['translate'] = $arrTranslate[$image['src']]['translate'];
                    }

                    unset($arrTranslate[$image['src']]);
                }
                elseif ($arrCurrent[$i]['translate'] != 'all')
                {
                    unset($arrCurrent[$i]);
                }
            }

            // Add remaining parent image to the list
            if (!empty($arrTranslate))
            {
                $arrCurrent = array_merge($arrCurrent, array_values($arrTranslate));
            }

            $arrCurrent = array_values($arrCurrent);
        }
        else
        {
            $arrCurrent = array_values($arrTranslate);
        }

        return $arrCurrent;
    }
}
