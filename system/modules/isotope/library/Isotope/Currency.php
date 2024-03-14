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

class Currency
{
    /**
     * @var array
     */
    private static $currencies;

    /**
     * Check if currency is supported
     *
     * @param string $currencyCode The three character currency code according to ISO 4217
     *
     * @return bool
     */
    public static function isSupported($currencyCode)
    {
        static::load();

        return (static::$currencies[$currencyCode] !== null);
    }

    /**
     * Get the ISO number for a currency code
     *
     * @param string $currencyCode The three character currency code according to ISO 4217
     *
     * @return int
     * @throws \UnderflowException if the currency is not supported
     */
    public static function getIsoNumber($currencyCode)
    {
        static::load($currencyCode);

        return (int) static::$currencies[$currencyCode]['code'];
    }

    /**
     * Get label for a currency code
     *
     * @param string $currencyCode The three character currency code according to ISO 4217
     *
     * @return string
     */
    public static function getLabel($currencyCode)
    {
        return (string) $GLOBALS['TL_LANG']['CUR'][$currencyCode];
    }

    /**
     * Gets minor units precision given currency (e.g. 1 EUR = 100 Euro Cents)
     *
     * @param string $currencyCode The three character currency code according to ISO 4217
     *
     * @return int The number minor units (usually "2")
     * @throws \UnderflowException if the currency is not supported
     */
    public static function getMinorUnits($currencyCode)
    {
        static::load($currencyCode);

        return (int) static::$currencies[$currencyCode]['units'];
    }

    /**
     * Convert amount to minor unit (e.g. 100 EUR = 10000 Euro Cents)
     *
     * @param float  $amount       The amount as floating point value
     * @param string $currencyCode The three character currency code according to ISO 4217
     *
     * @return int The amount calculated in minor units
     * @throws \UnderflowException if the currency is not supported
     */
    public static function getAmountInMinorUnits($amount, $currencyCode)
    {
        return (int) round($amount * 10 ** static::getMinorUnits($currencyCode));
    }

    /**
     * Lazy-load currency information
     *
     * @param string $currencyCode
     * @throws \UnderflowException if the currency is not supported
     */
    private static function load($currencyCode = null)
    {
        if (null === static::$currencies) {
            static::$currencies = include(TL_ROOT . '/system/modules/isotope/config/currencies.php');
        }

        if (null !== $currencyCode && !isset(static::$currencies[$currencyCode])) {
            throw new \UnderflowException('Currency code "' . $currencyCode . '" is not supported.');
        }
    }
}
