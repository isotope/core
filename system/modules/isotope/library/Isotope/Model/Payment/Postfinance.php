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

namespace Isotope\Model\Payment;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
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
     * Prepare PSP params
     * @param   Order
     * @return  array
     */
    protected function preparePSPParams($objOrder)
    {
        $arrParams = parent::preparePSPParams($objOrder);
        $arrParams = array_merge($arrParams, $this->prepareFISParams($objOrder));

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
            'ECOM_BILLTO_POSTAL_NAME_FIRST'     => $objBillingAddress->firstname,
            'ECOM_BILLTO_POSTAL_NAME_LAST'      => $objBillingAddress->lastname,
            'ECOM_CONSUMER_GENDER'              => $objBillingAddress->gender == 'male' ? 'M' : 'F',
            // This is mandatory if no P.O. Box and we don't have any
            'ECOM_SHIPTO_POSTAL_STREET_LINE1'   => $objShippingAddress->street_1,
            'ECOM_SHIPTO_POSTAL_POSTALCODE'     => $objShippingAddress->postal,
            'ECOM_SHIPTO_POSTAL_CITY'           => $objShippingAddress->city,
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE'    => strtoupper($objShippingAddress->country),

            'ECOM_SHIPTO_DOB'                   => date('d/m/Y', $objShippingAddress->dateOfBirth),
            // This key is mandatory and just has to be unique (20 chars)
            'REF_CUSTOMERID'                    => substr('psp_' . $this->id . '_' . $objOrder->id . '_' . $objOrder->uniqid, 0, 20)

            // We do not add "ECOM_SHIPTO_COMPANY" here because B2B sometimes may require up to 24 hours
            // to check solvency which is not acceptable for an online shop
        );

        $arrOrder = array();
        $i = 1;

        // Need to take the items from the cart as they're not transferred to the order here yet
        foreach (Isotope::getCart()->getItems() as $objItem) {

            $objPrice = $objItem->getProduct()->getPrice();
            $fltVat = Isotope::roundPrice((100 / $objPrice->getNetAmount() * $objPrice->getGrossAmount()) - 100, false);

            $arrOrder['ITEMID' . $i]        = $objItem->id;
            $arrOrder['ITEMNAME' . $i]      = $objItem->getName();
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
