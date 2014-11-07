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
use Isotope\Isotope;
use Isotope\Model\Payment;


/**
 * Class Postfinance
 *
 * Handle Postfinance (Swiss Post) payments
 * @copyright  Isotope eCommerce Workgroup 2009-2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class Postfinance extends PSP implements IsotopePayment, IsotopePostsale
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
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'AMOUNT',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COMPLUS',
        'CREATION_STATUS',
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
        'ENCCARDNO',
        'FXAMOUNT',
        'FXCURRENCY',
        'IP',
        'IPCTY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
        'NCERRORCARDNO',
        'NCERRORCN',
        'NCERRORCVC',
        'NCERRORED',
        'ORDERID',
        'PAYID',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
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
     * @param   Order
     * @param   Module
     * @return  array
     */
    protected function preparePSPParams($objOrder, $objModule)
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

        // @todo: Activate this as soon as PostFinance has fixed the issues with FIS
        // integration on their side
        //$arrParams = array_merge($arrParams, $this->prepareFISParams($objOrder));

        return $arrParams;
    }

    /**
     * Prepare FIS params
     * @param   Order
     * @return  array
     */
    private function prepareFISParams($objOrder)
    {
        $objBillingAddress  = $objOrder->getBillingAddress();
        $objShippingAddress = $objOrder->getShippingAddress();

        $arrInvoice = array
        (
            // Mandatory fields
            'ECOM_BILLTO_POSTAL_NAME_FIRST'     => substr($objBillingAddress->firstname, 0, 50),
            'ECOM_BILLTO_POSTAL_NAME_LAST'      => substr($objBillingAddress->lastname, 0, 50),
            'ECOM_SHIPTO_POSTAL_STREET_LINE1'   => $objShippingAddress->street_1,
            'ECOM_SHIPTO_POSTAL_POSTALCODE'     => $objShippingAddress->postal,
            'ECOM_SHIPTO_POSTAL_CITY'           => $objShippingAddress->city,
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE'    => strtoupper($objShippingAddress->country),
            'ECOM_SHIPTO_DOB'                   => date('d/m/Y', $objShippingAddress->dateOfBirth),
            // This key is mandatory and just has to be unique (17 chars)
            'REF_CUSTOMERID'                    => substr('psp_' . $this->id . '_' . $objOrder->id . '_' . $objOrder->uniqid, 0, 17),

            // Additional fields, not mandatory
            'ECOM_CONSUMER_GENDER'              => $objBillingAddress->gender == 'male' ? 'M' : 'F',

            // We do not add "ECOM_SHIPTO_COMPANY" here because B2B sometimes may require up to 24 hours
            // to check solvency which is not acceptable for an online shop
        );

        $arrOrder = array();
        $i = 1;

        // Need to take the items from the cart as they're not transferred to the order here yet
        // @todo this is no longer true, and the price should probably be taken from the collection item ($objItem->getPrice())
        foreach (Isotope::getCart()->getItems() as $objItem) {

            $objPrice = $objItem->getProduct()->getPrice();
            $fltVat = Isotope::roundPrice((100 / $objPrice->getNetAmount() * $objPrice->getGrossAmount()) - 100, false);

            $arrOrder['ITEMID' . $i]        = $objItem->id;
            $arrOrder['ITEMNAME' . $i]      = substr(\String::restoreBasicEntities(
                $objItem->getName()
            ), 40);
            $arrOrder['ITEMPRICE' . $i]     = $objPrice->getNetAmount();
            $arrOrder['ITEMQUANT' . $i]     = $objItem->quantity;
            $arrOrder['ITEMVATCODE' . $i]   = $fltVat . '%';
            $arrOrder['ITEMVAT' . $i]       = Isotope::roundPrice($objPrice->getGrossAmount() - $objPrice->getNetAmount(), false);
            $arrOrder['FACEXCL' . $i]       = $objPrice->getNetAmount();
            $arrOrder['FACTOTAL' . $i]      = $objPrice->getGrossAmount();

            ++$i;
        }

        return array_merge($arrInvoice, $arrOrder);
    }
}
