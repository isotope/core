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

use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Contao\Widget;
use Haste\Data\Plain;
use Haste\Util\Format;
use Isotope\Frontend\ProductAction\CartAction;
use Isotope\Frontend\ProductAction\FavoriteAction;
use Isotope\Frontend\ProductAction\ProductActionInterface;
use Isotope\Frontend\ProductAction\UpdateAction;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Config;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Favorites;
use Isotope\Model\ProductPrice;
use Isotope\Model\RequestCache;
use Isotope\Model\TaxClass;


/**
 * The base class for all Isotope components.
 */
class Isotope extends Controller
{

    /**
     * Isotope version
     */
    const VERSION = '2.8.16';

    /**
     * True if the system has been initialized
     * @var bool
     */
    protected static $blnInitialized = false;

    /**
     * Current cart instance
     * @var Cart
     */
    protected static $objCart;

    /**
     * Current config instance
     * @var Config
     */
    protected static $objConfig;

    /**
     * Current request cache instance
     * @var RequestCache
     */
    protected static $objRequestCache;


    public static function initialize()
    {
        if (static::$blnInitialized === false) {

            static::$blnInitialized = true;

            // Make sure field data is available
            Controller::loadDataContainer('tl_iso_product');
            System::loadLanguageFile('tl_iso_product');

            // Initialize request cache for product list filters
            if (Input::get('isorc') != '') {
                if (static::getRequestCache()->isEmpty()) {
                    global $objPage;
                    $objPage->noSearch = 1;

                } elseif (static::getRequestCache()->id != Input::get('isorc')) {
                    unset($_GET['isorc']);

                    // Unset the language parameter
                    if ($GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                        unset($_GET['language']);
                    }

                    $strQuery = http_build_query($_GET);
                    Controller::redirect(
                        preg_replace(
                            '/\?.*$/i',
                            '',
                            Environment::get('request') . ($strQuery ? '?' . $strQuery : '')
                        )
                    );
                }
            }
        }
    }

    /**
     * Get the currently active Isotope cart
     *
     * @return Cart|null
     */
    public static function getCart()
    {
        if (null === static::$objCart && 'FE' === TL_MODE) {
            static::initialize();
            if ((static::$objCart = Cart::findForCurrentStore()) !== null) {
                static::$objCart->mergeGuestCart();
            }
        }

        return static::$objCart;
    }

    /**
     * Gets the favorites collection for the currently logged in user
     *
     * @return Favorites|null
     */
    public static function getFavorites()
    {
        return Favorites::findForCurrentStore();
    }

    /**
     * Set the currently active Isotope cart
     *
     * @param Cart $objCart
     */
    public static function setCart(Cart $objCart)
    {
        static::$objCart = $objCart;
    }

    /**
     * Get the currently active Isotope configuration
     *
     * @return Config
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

                static::$objConfig = ('FE' === TL_MODE ? Config::findByRootPageOrFallback($objPage->rootId) : Config::findByFallback());
            }

            // No config at all, create empty model as fallback
            if (null === static::$objConfig) {
                static::$objConfig = new Config();
                trigger_error($GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration']);
            }
        }

        return static::$objConfig;
    }

    /**
     * Set the currently active Isotope configuration
     *
     * @param Config $objConfig
     */
    public static function setConfig(Config $objConfig = null)
    {
        static::$objConfig = $objConfig;
    }

    /**
     * Get active request cache
     *
     * @return RequestCache
     */
    public static function getRequestCache()
    {
        $cart = static::getCart();

        // The system has not been initialized yet, return a temporary empty object
        if (null === $cart) {
            return new RequestCache();
        }

        if (null === static::$objRequestCache) {
            static::$objRequestCache = RequestCache::findByIdAndStore(Input::get('isorc'), $cart->store_id);

            if (null === static::$objRequestCache) {
                static::$objRequestCache = new RequestCache();
                static::$objRequestCache->store_id = $cart->store_id;
            }
        }

        return static::$objRequestCache;
    }

    /**
     * Calculate price through hooks and foreign prices
     *
     * @param float  $fltPrice
     * @param object $objSource
     * @param string $strField
     * @param int    $intTaxClass
     * @param array  $arrAddresses
     * @param array  $arrOptions
     *
     * @return float
     */
    public static function calculatePrice(
        $fltPrice,
        $objSource,
        $strField,
        $intTaxClass = 0,
        array $arrAddresses = null,
        array $arrOptions = array()
    ) {
        if (empty($fltPrice) || !is_numeric($fltPrice)) {
            return 0;
        }

        // !HOOK: calculate price
        if (isset($GLOBALS['ISO_HOOKS']['calculatePrice']) && \is_array($GLOBALS['ISO_HOOKS']['calculatePrice'])) {
            foreach ($GLOBALS['ISO_HOOKS']['calculatePrice'] as $callback) {
                $fltPrice = System::importStatic($callback[0])->{$callback[1]}(
                    $fltPrice,
                    $objSource,
                    $strField,
                    $intTaxClass,
                    $arrOptions
                );
            }
        }

        $objConfig = static::getConfig();

        if ($objConfig->priceMultiplier != 1) {
            switch ($objConfig->priceCalculateMode) {
                case 'mul':
                    $fltPrice = $fltPrice * $objConfig->priceCalculateFactor;
                    break;

                case 'div':
                    $fltPrice = $fltPrice / $objConfig->priceCalculateFactor;
                    break;
            }
        }

        $sourceIsProduct = $objSource instanceof IsotopeProduct;
        $sourceIsPrice   = $objSource instanceof ProductPrice;

        if (!\is_array($arrAddresses) && ($sourceIsProduct || $sourceIsPrice)) {
            $product = $sourceIsPrice ? $objSource->getRelated('pid') : $objSource;

            $arrAddresses = array(
                'billing'  => Isotope::getCart()->getBillingAddress(),
                'shipping' => $product->isExemptFromShipping() ? Isotope::getCart()->getBillingAddress() : Isotope::getCart()->getShippingAddress(),
            );
        }

        // Possibly add/subtract tax
        /** @var TaxClass $objTaxClass */
        if ($intTaxClass > 0 && ($objTaxClass = TaxClass::findByPk($intTaxClass)) !== null) {
            $fltPrice = $objTaxClass->calculatePrice($fltPrice, $arrAddresses);
        }

        return static::roundPrice($fltPrice);
    }

    /**
     * Rounds a price according to store config settings
     *
     * @param float $fltValue                  original value
     * @param bool  $blnApplyRoundingIncrement apply rounding increment
     *
     * @return float rounded value
     */
    public static function roundPrice($fltValue, $blnApplyRoundingIncrement = true)
    {
        $objConfig = static::getConfig();

        if ($blnApplyRoundingIncrement && '0.05' === $objConfig->priceRoundIncrement) {
            $fltValue = round(20 * $fltValue) / 20;
        }

        return round($fltValue, $objConfig->priceRoundPrecision);
    }

    /**
     * Format given price according to store config settings
     *
     * @param float $fltPrice
     * @param bool  $blnApplyRoundingIncrement
     *
     * @return float
     */
    public static function formatPrice($fltPrice, $blnApplyRoundingIncrement = true)
    {
        // If price or override price is a string
        if (!is_numeric($fltPrice)) {
            return $fltPrice;
        }

        $fltPrice  = static::roundPrice($fltPrice, $blnApplyRoundingIncrement);
        $arrFormat = $GLOBALS['ISO_NUM'][static::getConfig()->currencyFormat];

        if (!\is_array($arrFormat)) {
            return $fltPrice;
        }

        return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
    }

    /**
     * Format given price according to store config settings, including currency representation
     *
     * @param float  $fltPrice
     * @param bool   $blnHtml
     * @param string $strCurrencyCode
     * @param bool   $blnApplyRoundingIncrement
     *
     * @return string
     */
    public static function formatPriceWithCurrency($fltPrice, $blnHtml = true, $strCurrencyCode = null, $blnApplyRoundingIncrement = true, Config $objConfig = null)
    {
        // If price or override price is a string
        if (!is_numeric($fltPrice)) {
            return $fltPrice;
        }

        $objConfig   = $objConfig ?: static::getConfig();
        $strCurrency = $strCurrencyCode ?: $objConfig->currency;
        $strPrice    = static::formatPrice($fltPrice, $blnApplyRoundingIncrement);
        $space       = $blnHtml ? '&nbsp;' : ' ';

        if ($objConfig->currencySymbol && $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency] != '') {
            $strCurrency = $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency];

            if (!$objConfig->currencySpace) {
                $space = '';
            }
        }

        if ($blnHtml) {
            $strCurrency = '<span class="currency">' . $strCurrency . '</span>';
        }

        if ('right' === $objConfig->currencyPosition) {
            return $strPrice . $space . $strCurrency;
        }

        return $strCurrency . $space . $strPrice;
    }

    /**
     * Format the number of items and return the items string
     *
     * @param int $intItems
     *
     * @return string
     */
    public static function formatItemsString($intItems)
    {
        if ($intItems == 1) {
            return $GLOBALS['TL_LANG']['MSC']['productSingle'];
        }

        $arrFormat = $GLOBALS['ISO_NUM'][static::getConfig()->currencyFormat];

        if (\is_array($arrFormat)) {
            $intItems = number_format($intItems, 0, $arrFormat[1], $arrFormat[2]);
        }

        return sprintf($GLOBALS['TL_LANG']['MSC']['productMultiple'], $intItems);
    }

    /**
     * Callback for isoButton Hook
     *
     * @param array          $arrButtons
     * @param IsotopeProduct $objProduct
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.5
     */
    public static function defaultButtons($arrButtons, IsotopeProduct $objProduct = null)
    {
        $actions = [
            new UpdateAction(),
            new CartAction(),
        ];

        if (true === FE_USER_LOGGED_IN) {
            $actions[] = new FavoriteAction();
        }

        /** @var ProductActionInterface $action */
        foreach ($actions as $action) {
            $arrButtons[$action->getName()] = array(
                'label' => $action->getLabel($objProduct),
                'callback' => [\get_class($action), 'handleSubmit'],
                'class'    => ($objProduct instanceof IsotopeProduct && \is_callable([$action, 'getClasses']) ? $action->getClasses($objProduct) : '')
            );
        }

        return $arrButtons;
    }

    /**
     * Validate a custom regular expression
     *
     * @param string  $strRegexp
     * @param mixed   $varValue
     *
     * @return bool
     */
    public static function validateRegexp($strRegexp, $varValue, Widget $objWidget)
    {
        switch ($strRegexp) {
            case 'price':
                if (!preg_match('/^[\d .-]*$/', $varValue)) {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['digit'], $objWidget->label));
                }

                return true;

            case 'discount':
                if (!preg_match('/^[-+]\d+(\.\d+)?%?$/', $varValue)) {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['discount'], $objWidget->label));
                }

                return true;

            case 'surcharge':
                if (!preg_match('/^-?\d+(\.\d+)?%?$/', $varValue)) {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['surcharge'], $objWidget->label));
                }

                return true;
        }

        return false;
    }

    /**
     * Format options label and value
     *
     * @param array  $arrData
     * @param string $strTable
     * @param bool   $blnSkipEmpty
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0
     */
    public static function formatOptions(array $arrData, $strTable = 'tl_iso_product', $blnSkipEmpty = true)
    {
        $arrOptions = array();

        foreach ($arrData as $field => $value) {
            if ($blnSkipEmpty && ($value == '' || $value == '-')) {
                continue;
            }

            $arrOptions[$field] = array
            (
                'label' => Format::dcaLabel($strTable, $field),
                'value' => Controller::replaceInsertTags(Format::dcaValue($strTable, $field, $value)),
            );
        }

        return $arrOptions;
    }

    /**
     * Format product configuration using \Haste\Data
     *
     * @param array                  $arrConfig
     * @param IsotopeProduct|Product $objProduct
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0
     */
    public static function formatProductConfiguration(array $arrConfig, IsotopeProduct $objProduct)
    {
        Product::setActive($objProduct);

        $strTable = Product::getTable();

        foreach ($arrConfig as $k => $v) {

            /** @var \Isotope\Model\Attribute $objAttribute */
            if (($objAttribute = $GLOBALS['TL_DCA'][$strTable]['attributes'][$k]) !== null
                && $objAttribute instanceof IsotopeAttributeWithOptions
            ) {

                /** @var Widget $strClass */
                $strClass = $objAttribute->getFrontendWidget();
                $arrField = $strClass::getAttributesFromDca(
                    $GLOBALS['TL_DCA'][$strTable]['fields'][$k],
                    $k,
                    $v,
                    $k,
                    $strTable,
                    $objProduct
                );

                $arrOptions = array();
                $values     = $v;

                if (!empty($arrField['options']) && \is_array($arrField['options'])) {

                    if (!\is_array($values)) {
                        $values = array($values);
                    }

                    $arrOptions = array_filter(
                        $arrField['options'],
                        function($option) use (&$values) {
                            if (($pos = array_search($option['value'], $values)) !== false) {
                                unset($values[$pos]);

                                return true;
                            }

                            return false;
                        }
                    );

                    $arrOptions = array_column($arrOptions, 'label');

                    if (!empty($values)) {
                        $arrOptions = array_merge($arrOptions, $values);
                    }
                }

                $formatted = implode(', ', $arrOptions);

            } else {
                $formatted = Format::dcaValue($strTable, $k, $v);
            }

            $arrConfig[$k] = new Plain(
                $v,
                Format::dcaLabel($strTable, $k),
                array (
                    'formatted' => Controller::replaceInsertTags($formatted),
                )
            );
        }

        Product::unsetActive();

        return $arrConfig;
    }
}
