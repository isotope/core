<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopePurchasableCollection;

/**
 * Handle Postfinance (Swiss Post) payments
 */
class Postfinance extends PSP
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_payment_postfinance';

    /**
     * SHA-OUT relevant fields
     * @var array
     */
    protected static $arrShaOut = array
    (
        'AAVADDRESS',
        'AAVCHECK',
        'AAVMAIL',
        'AAVNAME',
        'AAVPHONE',
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'AMOUNT',
        'BIC',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COLLECTOR_BIC',
        'COLLECTOR_IBAN',
        'COMPLUS',
        'CREATION_STATUS',
        'CREDITDEBIT',
        'CURRENCY',
        'CVCCHECK',
        'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATESOURCE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE',
        'DCC_VALIDHOURS',
        'DIGESTCARDNO',
        'ECI',
        'ED',
        'EMAIL',
        'ENCCARDNO',
        'FXAMOUNT',
        'FXCURRENCY',
        'IP',
        'IPCTY',
        'MANDATEID',
        'MOBILEMODE',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
        'ORDERID',
        'PAYID',
        'PAYMENT_REFERENCE',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
        'SEQUENCETYPE',
        'SIGNDATE',
        'STATUS',
        'SUBBRAND',
        'SUBSCRIPTION_ID',
        'TRXDATE',
        'VC'
    );

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethods()
    {
        return array(
            'CreditCard__American_Express'          => 'CreditCard - American Express',
            'CreditCard__Billy'                     => 'CreditCard - Billy',
            'CreditCard__CB'                        => 'CreditCard - CB',
            'CreditCard__Diners_Club'               => 'CreditCard - Diners Club',
            'CreditCard__JCB'                       => 'CreditCard - JCB',
            'CreditCard__MaestroUK'                 => 'CreditCard - MaestroUK',
            'CreditCard__MasterCard'                => 'CreditCard - MasterCard',
            'CreditCard__VISA'                      => 'CreditCard - VISA',
            'PostFinance_Card__PostFinance_Card'    => 'PostFinance Card',
            'PAYPAL__PAYPAL'                        => 'PayPal'
        );
    }

    /**
     * Prepare PSP params
     *
     * @param IsotopePurchasableCollection $objOrder
     * @param \Module                      $objModule
     *
     * @return array
     */
    protected function preparePSPParams(IsotopePurchasableCollection $objOrder, $objModule)
    {
        $arrParams = parent::preparePSPParams($objOrder, $objModule);

        // Add PostFinance specific PSP payment methods
        if ($this->psp_payment_method) {
            $chunks = explode('__', $this->psp_payment_method, 2);
            $arrParams = array_merge(
                $arrParams,
                array(
                    'PM'    => str_replace('_', ' ', $chunks[0]),
                    'BRAND' => str_replace('_', ' ', $chunks[1]),
                )
            );
        }

        return $arrParams;
    }
}
