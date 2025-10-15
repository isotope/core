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
use Contao\System;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductPrice;
use Isotope\Model\TaxClass;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Exception\MissingResourceException;


/**
 * The base class for all Isotope components.
 */
class Isotope extends Controller
{

    /**
     * Isotope version
     */
    public const VERSION = '3.x-dev';

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

    public static function initialize()
    {
        if (static::$blnInitialized === false) {

            static::$blnInitialized = true;

            // Make sure field data is available
            Controller::loadDataContainer('tl_iso_product');
            System::loadLanguageFile('tl_iso_product');
        }
    }

    /**
     * Get the currently active Isotope cart
     *
     * @return Cart|null
     */
    public static function getCart()
    {
        if (null === static::$objCart && CompatibilityHelper::isFrontend()) {
            static::initialize();
            if ((static::$objCart = Cart::findForCurrentStore()) !== null) {
                static::$objCart->mergeGuestCart();
            }
        }

        return static::$objCart;
    }

    /**
     * Set the currently active Isotope cart
     */
    public static function setCart(Cart $objCart)
    {
        static::$objCart = $objCart;
    }

    /**
     * Get the currently active Isotope configuration
     *
     * @return Config
     *
     * @deprecated
     */
    public static function getConfig(): Config
    {
        trigger_deprecation('isotope/core', '3.0', __METHOD__.' is deprecated.');

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return Config::findById($storeManager->getCurrentContext()->getConfigId());
    }

    /**
     * Set the currently active Isotope configuration
     */
    public static function setConfig(Config $objConfig = null)
    {
        static::$objConfig = $objConfig;
    }

    /**
     * Calculate price through hooks and foreign prices
     *
     * @param float  $fltPrice
     * @param object $objSource
     * @param string $strField
     * @param int    $intTaxClass
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

        if ($objConfig->priceCalculateFactor != 1) {
            $fltPrice = $fltPrice * $objConfig->priceCalculateFactor;
        }

        $sourceIsProduct = $objSource instanceof ProductInterface;
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

        if ($objConfig->currencySymbol) {
            try {
                $strCurrency = Currencies::getSymbol($objConfig->currency);

                if (!$objConfig->currencySpace) {
                    $space = '';
                }
            } catch (MissingResourceException) {}
        }

        if ($blnHtml) {
            $strCurrency = '<span class="currency">' . $strCurrency . '</span>';
        }

        if ('right' === $objConfig->currencyPosition) {
            return $strPrice . $space . $strCurrency;
        }

        return $strCurrency . $space . $strPrice;
    }
}
