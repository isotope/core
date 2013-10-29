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


/**
 * Class Viveum
 *
 * Handle VIVEUM payments
 * @copyright  Isotope eCommerce Workgroup 2009-2013
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class Viveum extends Payment implements IsotopePayment, IsotopePostsale
{
    /**
     * SHA-OUT relevant fields
     * @var array
     */
    private static $arrShaOut = array
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
        'IP',
        'IPCTY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
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
     * Process payment on confirmation page
     * @return  boolean
     */
    public function processPayment()
    {
        if (\Input::get('NCERROR') > 0) {
            \System::log('Order ID "' . \Input::get('orderID') . '" has NCERROR ' . \Input::get('NCERROR'), __METHOD__, TL_ERROR);

            return false;
        }

        if (($objOrder = Order::findByPk((int) \Input::get('orderID'))) === null) {
            \System::log('Order ID "' . \Input::get('orderID') . '" not found', __METHOD__, TL_ERROR);

            return false;
        }

        if (!$this->validateSHASign()) {
            \System::log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);

            return false;
        }

        // Validate payment data (see #2221)
        if ($objOrder->currency != \Input::post('currency') || $objOrder->getTotal() != \Input::post('amount')) {
            \System::log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        return true;
    }


    /**
     * Process post-sale requestion from the VIVEUM payment server.
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (\Input::post('NCERROR') > 0) {
            \System::log('Order ID "' . \Input::post('orderID') . '" has NCERROR ' . \Input::post('NCERROR'), __METHOD__, TL_ERROR);

            return;
        }

        $objCart = $objOrder->getRelated('source_collection_id');

        if (!$this->validateSHASign()) {
            \System::log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);

            return;
        }

        // Validate payment data (see #2221)
        if ($objOrder->currency != \Input::post('currency') || $objCart->getTotal() != \Input::post('amount')) {
            \System::log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);

            return;
        }

        if (!$objOrder->checkout()) {
            \System::log('Post-Sale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);
        $objOrder->save();
    }


    /**
     * {@inheritdoc}
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('orderID'));
    }


    /**
     * Return the payment form
     * @return  string
     */
    public function checkoutForm()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null) {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $arrParams = $this->preparePSPParams($objOrder);

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        $strSHASign = '';
        foreach($arrParams as $k => $v) {
            if ($v == '')
                continue;

            $strSHASign .= $k . '=' . htmlspecialchars_decode($v) . $this->psp_hash_in;
        }

        $arrParams['SHASIGN'] = strtoupper(sha1($strSHASign));

        $objTemplate = new \Isotope\Template('iso_payment_viveum');

        $objTemplate->action = 'https://viveum.v-psp.com/ncol/' . ($this->debug ? 'test' : 'prod') . '/orderstandard_utf8.asp';
        $objTemplate->params = $arrParams;
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2];
        $objTemplate->id = $this->id;

        return $objTemplate->parse();
    }

    /**
     * Prepare PSP params
     * @param   Order
     * @return  array
     */
    private function preparePSPParams($objOrder)
    {
        $strFailedUrl = \Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed');
        $objBillingAddress = $objOrder->getBillingAddress();

        return array
        (
            'PSPID'         => $this->psp_pspid,
            'ORDERID'       => $objOrder->id,
            'AMOUNT'        => round((Isotope::getCart()->getTotal() * 100)),
            'CURRENCY'      => Isotope::getConfig()->currency,
            'LANGUAGE'      => $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
            'CN'            => $objBillingAddress->firstname . ' ' . $objBillingAddress->lastname,
            'EMAIL'         => $objBillingAddress->email,
            'OWNERZIP'      => $objBillingAddress->postal,
            'OWNERADDRESS'  => $objBillingAddress->street_1,
            'OWNERADDRESS2' => $objBillingAddress->street_2,
            'OWNERCTY'      => $objBillingAddress->country,
            'OWNERTOWN'     => $objBillingAddress->city,
            'OWNERTELNO'    => $objBillingAddress->phone,
            'ACCEPTURL'     => \Environment::get('base') . \Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, \Isotope\Module\Checkout::generateUrlForStep('complete')),
            'DECLINEURL'    => $strFailedUrl,
            'EXCEPTIONURL'  => $strFailedUrl,
            'TP'            => $this->psp_dynamic_template ?: '',
            'PARAMPLUS'     => 'mod=pay&amp;id=' . $this->id,
        );
    }


    /**
     * Validate SHA-OUT signature
     * @return  boolean
     */
    private function validateSHASign()
    {
        $strSHASign = '';
        $arrParams = array();

        foreach (array_keys($_POST) as $key) {
            if (in_array(strtoupper($key), self::$arrShaOut)) {
                $arrParams[$key] = \Input::post($key);
            }
        }

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        foreach($arrParams as $k => $v ) {
            if ($v == '') {
                continue;
            }

            $strSHASign .= strtoupper($k) . '=' . $v . $this->psp_hash_out;
        }

        if (\Input::post('SHASIGN') == strtoupper(sha1($strSHASign))) {
            return true;
        }

        return false;
    }
}
