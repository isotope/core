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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;


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
     * Creates the template instance
     * @return  \FrontendTemplate
     */
    protected function prepareTemplate()
    {
        $objTemplate = new Template('iso_payment_psp');
        $objTemplate->action = 'https://e-payment.postfinance.ch/ncol/' . ($this->debug ? 'test' : 'prod') . '/orderstandard_utf8.asp';

        return $objTemplate;
    }

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

        $arrInvoice = array
        (
            'ECOM_BILLTO_POSTAL_NAME_FIRST'     => $objBillingAddress->firstname,
            'ECOM_BILLTO_POSTAL_NAME_LAST'      => $objBillingAddress->lastname,
            'OWNERADDRESS'                      => $objBillingAddress->street_1,
            // @todo: is this mandatory?
            'OWNERADDRESS2'                     => $objBillingAddress->street_2,
            'OWNERZIP'                          => $objBillingAddress->postal,
            'OWNERTOWN'                         => $objBillingAddress->city,
            'OWNERCTY'                          => strtoupper($objBillingAddress->country),
            'ECOM_SHIPTO_DOB'                   => date('d/m/Y', $objBillingAddress->dateOfBirth),
            'ECOM_CONSUMER_GENDER'              => $objBillingAddress->gender == 'male' ? 'M' : 'F',
            // This key is mandatory and just has to be unique
            'REF_CUSTOMERID'                    => 'psp_' . $this->id . '_' . $objOrder->id
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

            $i++;
        }

        return array_merge($arrInvoice, $arrOrder);
    }
}
