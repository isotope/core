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
use Contao\Widget;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\Haste\Data\Plain;
use Isotope\Haste\Util\Format;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Config;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductPrice;
use Isotope\Model\TaxClass;


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

                static::$objConfig = (CompatibilityHelper::isFrontend() ? Config::findByRootPageOrFallback($objPage ? $objPage->rootId : 0) : Config::findByFallback());
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
     * Format options label and value
     *
     * @param string $strTable
     * @param bool   $blnSkipEmpty
     *
     * @return array
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
                'value' => \Contao\System::getContainer()->get('contao.insert_tag.parser')->replace(Format::dcaValue($strTable, $field, $value)),
            );
        }

        return $arrOptions;
    }

    /**
     * Format product configuration using \Haste\Data
     *
     * @param IsotopeProduct|Product $objProduct
     *
     * @return array
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0
     */
    public static function formatProductConfiguration(array $arrConfig, IsotopeProduct $objProduct)
    {
        Product::setActive($objProduct);

        $strTable = Product::getTable();

        /** @var AttributeRegistry $attributeRegistry */
        $attributeRegistry = System::getContainer()->get(AttributeRegistry::class);

        foreach ($arrConfig as $k => $v) {
            if (($objAttribute = $attributeRegistry->get($k)) !== null
                && $objAttribute instanceof OptionsAttributeInterface
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
                    'formatted' => \Contao\System::getContainer()->get('contao.insert_tag.parser')->replace($formatted),
                )
            );
        }

        Product::unsetActive();

        return $arrConfig;
    }
}
