<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;


class EPay extends Payment implements IsotopePayment, IsotopePostsale
{

    /**
     * ePay language IDs
     */
    private static $arrLanguages = array(
        'da' => 1,  // Danish
        'en' => 2,  // English
        'sv' => 3,  // Swedish
        'no' => 4,  // Norwegian
        'kl' => 5,  // Greenlandic
        'is' => 6,  // Icelandic
        'de' => 7,  // German
        'fi' => 8,  // Finnish
        'es' => 9,  // Spanish
        'fr' => 10, // French
        'pl' => 11, // Polish
        'it' => 12, // Italian
        'nl' => 13, // Dutch
    );

    /**
     * Check the cart currency for ePay support
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (!static::supportsCurrency(Isotope::getConfig()->currency)) {
            return false;
        }

        return parent::isAvailable();
    }

    /**
     * Process payment on the confirmation page
     *
     * @param IsotopeProductCollection $objOrder
     * @param \Module                  $objModule
     *
     * @return bool|mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$this->validatePayment($objOrder)) {
            return false;
        }

        if (!$objOrder->checkout()) {
            \System::log('Checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
            return false;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        return true;
    }


    /**
     * Process ePay callback
     *
     * @param IsotopeProductCollection $objOrder
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if ($this->validatePayment($objOrder)) {
            if (!$objOrder->checkout()) {
                \System::log('Postsale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                return false;
            }

            $objOrder->date_paid = time();
            $objOrder->updateOrderStatus($this->new_order_status);

            $objOrder->save();
        }
    }

    /**
     * Get the order object in a postsale request
     *
     * @return  IsotopeProductCollection|null
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) \Input::get('orderid'));
    }

    /**
     * Return the ePay form
     *
     * @param IsotopeProductCollection $objOrder
     * @param \Module                  $objModule
     *
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $objTemplate = new \Isotope\Template('iso_payment_epay');
        $objTemplate->setData($this->arrData);

        $objTemplate->currency = $objOrder->currency;
        $objTemplate->amount = static::amountInMinorUnits($objOrder->getTotal(), $objOrder->currency);
        $objTemplate->orderid = $objOrder->id;
        $objTemplate->instantcapture = ($this->trans_type == 'capture' ? '1' : '0');
        $objTemplate->callbackurl = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->accepturl = \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder);
        $objTemplate->cancelurl = \Environment::get('base') . $objModule->generateUrlForStep('failed');
        $objTemplate->language = (int) static::$arrLanguages[substr($GLOBALS['TL_LANGUAGE'], 0, 2)];

        return $objTemplate->parse();
    }

    /**
     * Validate input parameters and hash
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return bool
     */
    protected function validatePayment(IsotopeProductCollection $objOrder)
    {
        $arrValues = $_GET;
        unset($arrValues['hash']);
        unset($arrValues['auto_item']);
        unset($arrValues['step']);

        $strHash = md5(implode('', $arrValues) . $this->epay_secretkey);
        $intAmount = static::amountInMinorUnits($objOrder->getTotal(), $objOrder->currency);

        if ($strHash != \Input::get('hash')) {
            \System::log('Invalid hash for ePay payment. See system/logs/isotope_epay.log for more details.', __METHOD__, TL_ERROR);

            log_message(
                sprintf(
                    "Invalid hash for ePay payment:\ngot %s, expected %s\nParameters: %s\n\n",
                    \Input::get('hash'),
                    $strHash,
                    print_r($arrValues, true)
                ),
                'isotope_epay.log'
            );

            return false;
        }

        if (static::getCurrencyNumber($objOrder->currency) != \Input::get('currency') || $intAmount != \Input::get('amount')) {
            \System::log('Currency or amount does not match order.  See system/logs/isotope_epay.log for more details.', __METHOD__, TL_ERROR);

            log_message(
                sprintf(
                    "Currency or amount does not match order:\nCurrency: got %s (%s), expected %s\nAmount: got %s, expected %s\n\n",
                    \Input::get('currency'),
                    static::getCurrencyNumber($objOrder->currency),
                    $objOrder->currency,
                    \Input::get('amount'),
                    $intAmount
                ),
                'isotope_epay.log'
            );

            return false;
        }

        return true;
    }


    private static function getCurrency($strCode)
    {
        static $arrCurrencies = array(
            'AFA' => array('code' => 4,   'units' => 2), // Afghani
            'ALL' => array('code' => 8,   'units' => 2), // Leck
            'DZD' => array('code' => 12,  'units' => 2), // Algerian Dinar
            'ADP' => array('code' => 20,  'units' => 0), // Andorran Peseta
            'AZM' => array('code' => 31,  'units' => 2), // Azerbaijanian Manat
            'ARS' => array('code' => 32,  'units' => 2), // Argentine Peso
            'AUD' => array('code' => 36,  'units' => 2), // Australian Dollar
            'BSD' => array('code' => 44,  'units' => 2), // Bahamian Dollar
            'BHD' => array('code' => 48,  'units' => 3), // Bahraini Dinar
            'BDT' => array('code' => 50,  'units' => 2), // Taka
            'AMD' => array('code' => 51,  'units' => 2), // Armenian Dram
            'BBD' => array('code' => 52,  'units' => 2), // Barbados Dollar
            'BMD' => array('code' => 60,  'units' => 2), // Bermudian Dollar
            'BTN' => array('code' => 64,  'units' => 2), // Ngultrum
            'BOB' => array('code' => 68,  'units' => 2), // Boliviano
            'BWP' => array('code' => 72,  'units' => 2), // Pula
            'BZD' => array('code' => 84,  'units' => 2), // Belize Dollar
            'SBD' => array('code' => 90,  'units' => 2), // Solomon Islands Dollar
            'BND' => array('code' => 96,  'units' => 2), // Brunei Dollar
            'BGL' => array('code' => 100, 'units' => 2), // Lev
            'MMK' => array('code' => 104, 'units' => 2), // Kyat
            'BIF' => array('code' => 108, 'units' => 0), // Burundi Franc
            'KHR' => array('code' => 116, 'units' => 2), // Riel
            'CAD' => array('code' => 124, 'units' => 2), // Canadian Dollar
            'CVE' => array('code' => 132, 'units' => 2), // Cape Verde Escudo
            'KYD' => array('code' => 136, 'units' => 2), // Cayman Islands Dollar
            'LKR' => array('code' => 144, 'units' => 2), // Sri Lanka Rupee
            'CLP' => array('code' => 152, 'units' => 0), // Chilean Peso
            'CNY' => array('code' => 156, 'units' => 2), // Yuan Renminbi
            'COP' => array('code' => 170, 'units' => 2), // Colombian Peso
            'KMF' => array('code' => 174, 'units' => 0), // Comoro Franc
            'CRC' => array('code' => 188, 'units' => 2), // Costa Rican Colon
            'HRK' => array('code' => 191, 'units' => 2), // Croatian kuna
            'CUP' => array('code' => 192, 'units' => 2), // Cuban Peso
            'CYP' => array('code' => 196, 'units' => 2), // Cyprus Pound
            'CZK' => array('code' => 203, 'units' => 2), // Czech Koruna
            'DKK' => array('code' => 208, 'units' => 2), // Danish Krone
            'DOP' => array('code' => 214, 'units' => 2), // Dominican Peso
            'ECS' => array('code' => 218, 'units' => 2), // Sucre
            'SVC' => array('code' => 222, 'units' => 2), // El Salvador Colon
            'ETB' => array('code' => 230, 'units' => 2), // Ethiopian Birr
            'ERN' => array('code' => 232, 'units' => 2), // Nakfa
            'EEK' => array('code' => 233, 'units' => 2), // Kroon
            'FKP' => array('code' => 238, 'units' => 2), // Falkland Islands Pound
            'FJD' => array('code' => 242, 'units' => 2), // Fiji Dollar
            'DJF' => array('code' => 262, 'units' => 0), // Djibouti Franc
            'GMD' => array('code' => 270, 'units' => 2), // Dalasi
            'GHC' => array('code' => 288, 'units' => 2), // Cedi
            'GIP' => array('code' => 292, 'units' => 2), // Gibraltar Pound
            'GTQ' => array('code' => 320, 'units' => 2), // Quetzal
            'GNF' => array('code' => 324, 'units' => 0), // Guinea Franc
            'GYD' => array('code' => 328, 'units' => 2), // Guyana Dollar
            'HTG' => array('code' => 332, 'units' => 2), // Gourde
            'HNL' => array('code' => 340, 'units' => 2), // Lempira
            'HKD' => array('code' => 344, 'units' => 2), // Hong Kong Dollar
            'HUF' => array('code' => 348, 'units' => 2), // Forint
            'ISK' => array('code' => 352, 'units' => 2), // Iceland Krona
            'INR' => array('code' => 356, 'units' => 2), // Indian Rupee
            'IDR' => array('code' => 360, 'units' => 2), // Rupiah
            'IRR' => array('code' => 364, 'units' => 2), // Iranian Rial
            'IQD' => array('code' => 368, 'units' => 3), // Iraqi Dinar
            'ILS' => array('code' => 376, 'units' => 2), // New Israeli Sheqel
            'JMD' => array('code' => 388, 'units' => 2), // Jamaican Dollar
            'JPY' => array('code' => 392, 'units' => 0), // Yen
            'KZT' => array('code' => 398, 'units' => 2), // Tenge
            'JOD' => array('code' => 400, 'units' => 3), // Jordanian Dinar
            'KES' => array('code' => 404, 'units' => 2), // Kenyan Shilling
            'KPW' => array('code' => 408, 'units' => 2), // North Korean Won
            'KRW' => array('code' => 410, 'units' => 0), // Won
            'KWD' => array('code' => 414, 'units' => 3), // Kuwaiti Dinar
            'KGS' => array('code' => 417, 'units' => 2), // Som
            'LAK' => array('code' => 418, 'units' => 2), // Kip
            'LBP' => array('code' => 422, 'units' => 2), // Lebanese Pound
            'LSL' => array('code' => 426, 'units' => 2), // Loti
            'LVL' => array('code' => 428, 'units' => 2), // Latvian Lats
            'LRD' => array('code' => 430, 'units' => 2), // Liberian Dollar
            'LYD' => array('code' => 434, 'units' => 3), // Lybian Dinar
            'LTL' => array('code' => 440, 'units' => 2), // Lithuanian Litus
            'MOP' => array('code' => 446, 'units' => 2), // Pataca
            'MGF' => array('code' => 450, 'units' => 0), // Malagasy Franc
            'MWK' => array('code' => 454, 'units' => 2), // Kwacha
            'MYR' => array('code' => 458, 'units' => 2), // Malaysian Ringgit
            'MVR' => array('code' => 462, 'units' => 2), // Rufiyaa
            'MTL' => array('code' => 470, 'units' => 2), // Maltese Lira
            'MRO' => array('code' => 478, 'units' => 2), // Ouguiya
            'MUR' => array('code' => 480, 'units' => 2), // Mauritius Rupee
            'MXN' => array('code' => 484, 'units' => 2), // Mexican Peso
            'MNT' => array('code' => 496, 'units' => 2), // Tugrik
            'MDL' => array('code' => 498, 'units' => 2), // Moldovan Leu
            'MAD' => array('code' => 504, 'units' => 2), // Moroccan Dirham
            'MZM' => array('code' => 508, 'units' => 2), // Metical
            'OMR' => array('code' => 512, 'units' => 3), // Rial Omani
            'NAD' => array('code' => 516, 'units' => 2), // Namibia Dollar
            'NPR' => array('code' => 524, 'units' => 2), // Nepalese Rupee
            'ANG' => array('code' => 532, 'units' => 2), // Netherlands Antillan Guilder
            'AWG' => array('code' => 533, 'units' => 2), // Aruban Guilder
            'VUV' => array('code' => 548, 'units' => 0), // Vatu
            'NZD' => array('code' => 554, 'units' => 2), // New Zealand Dollar
            'NIO' => array('code' => 558, 'units' => 2), // Cordoba Oro
            'NGN' => array('code' => 566, 'units' => 2), // Naira
            'NOK' => array('code' => 578, 'units' => 2), // Norwegian Krone
            'PKR' => array('code' => 586, 'units' => 2), // Pakistan Rupee
            'PAB' => array('code' => 590, 'units' => 2), // Balboa
            'PGK' => array('code' => 598, 'units' => 2), // Kina
            'PYG' => array('code' => 600, 'units' => 0), // Guarani
            'PEN' => array('code' => 604, 'units' => 2), // Nuevo Sol
            'PHP' => array('code' => 608, 'units' => 2), // Philippine Peso
            'GWP' => array('code' => 624, 'units' => 2), // Guinea-Bissau Peso
            'TPE' => array('code' => 626, 'units' => 0), // Timor Escudo
            'QAR' => array('code' => 634, 'units' => 2), // Qatari Rial
            'ROL' => array('code' => 642, 'units' => 2), // Leu
            'RUB' => array('code' => 643, 'units' => 2), // Russian Ruble
            'RWF' => array('code' => 646, 'units' => 0), // Rwanda Franc
            'SHP' => array('code' => 654, 'units' => 2), // Saint Helena Pound
            'STD' => array('code' => 678, 'units' => 2), // Dobra
            'SAR' => array('code' => 682, 'units' => 2), // Saudi Riyal
            'SCR' => array('code' => 690, 'units' => 2), // Seychelles Rupee
            'SLL' => array('code' => 694, 'units' => 2), // Leone
            'SGD' => array('code' => 702, 'units' => 2), // Singapore Dollar
            'SKK' => array('code' => 703, 'units' => 2), // Slovak Koruna
            'VND' => array('code' => 704, 'units' => 2), // Dong
            'SIT' => array('code' => 705, 'units' => 2), // Tolar
            'SOS' => array('code' => 706, 'units' => 2), // Somali Shilling
            'ZAR' => array('code' => 710, 'units' => 2), // Rand
            'ZWD' => array('code' => 716, 'units' => 2), // Zimbabwe Dollar
            'SDD' => array('code' => 736, 'units' => 2), // Sudanese Dinar
            'SRG' => array('code' => 740, 'units' => 2), // Suriname Guilder
            'SZL' => array('code' => 748, 'units' => 2), // Lilangeni
            'SEK' => array('code' => 752, 'units' => 2), // Swedish Krona
            'CHF' => array('code' => 756, 'units' => 2), // Swiss Franc
            'SYP' => array('code' => 760, 'units' => 2), // Syrian Pound
            'THB' => array('code' => 764, 'units' => 2), // Baht
            'TOP' => array('code' => 776, 'units' => 2), // Pa´anga
            'TTD' => array('code' => 780, 'units' => 0), // Trinidad and Tobago Dollar
            'AED' => array('code' => 784, 'units' => 2), // UAE Dirham
            'TND' => array('code' => 788, 'units' => 3), // Tunisian Dinar
            'TRL' => array('code' => 792, 'units' => 0), // Turkish Lira
            'TMM' => array('code' => 795, 'units' => 2), // Manat
            'UGX' => array('code' => 800, 'units' => 2), // Uganda Shilling
            'MKD' => array('code' => 807, 'units' => 2), // Denar
            'RUR' => array('code' => 810, 'units' => 2), // Russian Ruble
            'EGP' => array('code' => 818, 'units' => 2), // Egyptian Pound
            'GBP' => array('code' => 826, 'units' => 2), // Pound Sterling
            'TZS' => array('code' => 834, 'units' => 2), // Tanzanian Shilling
            'USD' => array('code' => 840, 'units' => 2), // US Dollar
            'UYU' => array('code' => 858, 'units' => 2), // Peso Uruguayo
            'UZS' => array('code' => 860, 'units' => 2), // Uzbekistan Sum
            'VEB' => array('code' => 862, 'units' => 2), // Bolivar
            'YER' => array('code' => 886, 'units' => 2), // Yemeni Rial
            'YUM' => array('code' => 891, 'units' => 2), // Yugoslavian Dinar
            'ZMK' => array('code' => 894, 'units' => 2), // Kwacha
            'TWD' => array('code' => 901, 'units' => 2), // New Taiwan Dollar
            'TRY' => array('code' => 949, 'units' => 2), // New Turkish Lira
            'XAF' => array('code' => 950, 'units' => 0), // CFA Franc BEAC
            'XCD' => array('code' => 951, 'units' => 2), // East Caribbean Dollar
            'XOF' => array('code' => 952, 'units' => 0), // CFA Franc BCEAO
            'XPF' => array('code' => 953, 'units' => 0), // CFP Franc
            'TJS' => array('code' => 972, 'units' => 2), // Somoni
            'AOA' => array('code' => 973, 'units' => 2), // Kwanza
            'BYR' => array('code' => 974, 'units' => 0), // Belarussian Ruble
            'BGN' => array('code' => 975, 'units' => 2), // Bulgarian Lev
            'CDF' => array('code' => 976, 'units' => 2), // Franc Congolais
            'BAM' => array('code' => 977, 'units' => 2), // Convertible Marks
            'EUR' => array('code' => 978, 'units' => 2), // Euro
            'MXV' => array('code' => 979, 'units' => 2), // Mexican Unidad de Inversion (UDI)
            'UAH' => array('code' => 980, 'units' => 2), // Hryvnia
            'GEL' => array('code' => 981, 'units' => 2), // Lari
            'ECV' => array('code' => 983, 'units' => 2), // Unidad de Valor Constante (UVC)
            'BOV' => array('code' => 984, 'units' => 2), // Mvdol
            'PLN' => array('code' => 985, 'units' => 2), // Zloty
            'BRL' => array('code' => 986, 'units' => 2), // Brazilian Real
            'CLF' => array('code' => 990, 'units' => 0), // Unidades de fomento

        );

        return $arrCurrencies[$strCode];
    }

    /**
     * Get the ePay currency number for a currency code
     *
     * @param string $strCode
     *
     * @return int
     */
    private static function getCurrencyNumber($strCode)
    {
        $arrCurrency = static::getCurrency($strCode);

        if (null === $arrCurrency) {
            throw new \InvalidArgumentException('Currency code "' . $strCode . '" is not supported');
        }

        return (int) $arrCurrency['code'];
    }

    /**
     * Check if currency is supported by ePay
     *
     * @param string $strCode
     *
     * @return bool
     */
    private static function supportsCurrency($strCode)
    {
        return (static::getCurrency($strCode) !== null);
    }

    /**
     * Convert amount to minor unit
     *
     * @param float $fltAmount
     * @param string $strCurrency
     *
     * @return int
     */
    private static function amountInMinorUnits($fltAmount, $strCurrency)
    {
        $arrCurrency = static::getCurrency($strCurrency);

        if (null === $arrCurrency) {
            throw new \InvalidArgumentException('Currency code "' . $strCurrency . '" is not supported');
        }

        return (int) round($fltAmount * pow(10, $arrCurrency['units']));
    }
}
