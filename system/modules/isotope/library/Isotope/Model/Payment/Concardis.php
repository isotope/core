<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

class Concardis extends PSP
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_payment_concardis';

    /**
     * SHA-OUT relevant fields
     * @var array
     */
    protected static $arrShaOut = array
    (
        'ACCEPTANCE',
        'AMOUNT',
        'BIC',
        'BIN',
        'BRAND',
        'CARDNO',
        'CN',
        'COLLECTOR_BIC',
        'COLLECTOR_IBAN',
        'COMPLUS',
        'CREDITDEBIT',
        'CURRENCY',
        'ECI',
        'ED',
        'EMAIL',
        'FXAMOUNT',
        'FXCURRENCY',
        'IP',
        'MANDATEID',
        'MOBILEMODE',
        'NCERROR',
        'ORDERID',
        'PAYID',
        'PAYIDSUB',
        'PAYLIBIDREQUEST',
        'PAYLIBTRANSID',
        'PAYMENT_REFERENCE',
        'PM',
        'SEQUENCETYPE',
        'SIGNDATE',
        'STATUS',
        'SUBBRAND',
        'TRXDATE',
        'WALLET',
    );

    /**
     * @inheritdoc
     */
    public function getPaymentMethods()
    {
        return [];
    }
}
