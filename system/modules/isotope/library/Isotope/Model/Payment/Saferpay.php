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

use Isotope\Interfaces\IsotopeOrderStatusAware;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;

/**
 * Saferpay payment method
 *
 * @property string $saferpay_accountid
 * @property string $saferpay_description
 * @property string $saferpay_vtconfig
 * @property array  $saferpay_paymentmethods
 */
class Saferpay extends Postsale implements IsotopeOrderStatusAware
{
    protected $objXML;

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        if (!$this->validateXML($objOrder)) {
            return;
        }

        // Get the Payment URL from the saferpay hosting server
        $objRequest = new \Request();
        $objRequest->send($this->getApiUrl('VerifyPayConfirm.asp') . "?DATA=" . urlencode($this->getPostData()) . "&SIGNATURE=" . urlencode(\Input::post('SIGNATURE')));

        // Stop if verification is not working
        if (0 !== strpos(strtoupper($objRequest->response), 'OK:')) {
            \System::log(sprintf('Payment not successfull. See log files for further details.'), __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('Payment not successfull. Message was: "%s".', $objRequest->response));

            return;
        }

        $arrResponse = array();
        parse_str(substr($objRequest->response, 3), $arrResponse);

        // Store request data in order for future references
        $arrPayment = deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $this->getPostData();
        $arrPayment['PAYCONFIRM'] = $arrResponse;

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->payment_data = $arrPayment;
        $objOrder->save();

        // everything has been okay so far and the debit has been authorized. We capture it now if this is requested (usually it is).
        if ('auth' !== $this->trans_type) {
            $this->sendPayComplete($arrPayment['PAYCONFIRM']['ID'], $arrPayment['PAYCONFIRM']['TOKEN']);
            $objOrder->setDatePaid(time());
        }

        $objOrder->updateOrderStatus($this->new_order_status);
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) $this->getPostValue('ORDERID'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        // Get redirect url
        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $objRequest->send($this->getApiUrl('CreatePayInit.asp'), http_build_query($this->generatePaymentPostData($objOrder), null, '&'), 'POST');

        if ((int) $objRequest->code !== 200 || 0 === strpos($objRequest->response, 'ERROR:')) {
            \System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response));

            Checkout::redirectToStep('failed');
        }

        $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="1; URL=' . $objRequest->response . '">';

        return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<p><a href="' . $objRequest->response . '">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2] . '</a></p>';
    }

    /**
     * Update order on Saferpay terminal when changing order status in backend
     *
     * @param Order       $objOrder
     * @param int         $intOldStatus
     * @param OrderStatus $objNewStatus
     */
    public function onOrderStatusUpdate(Order $objOrder, $intOldStatus, OrderStatus $objNewStatus)
    {
        if ('capture' === $objNewStatus->saferpay_status) {

            $arrPayment = deserialize($objOrder->payment_data, true);
            $blnResult = $this->sendPayComplete($arrPayment['PAYCONFIRM']['ID'], $arrPayment['PAYCONFIRM']['TOKEN']);

            if ('BE' === TL_MODE) {
                if ($blnResult) {
                    \Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusSuccess']);
                } else {
                    \Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusError']);
                }
            }

        } elseif ('cancel' === $objNewStatus->saferpay_status && 'BE' === TL_MODE) {
            \Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusCancel']);
        }
    }

    /**
     * Generate POST data to initialize payment
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return array
     */
    protected function generatePaymentPostData(IsotopeProductCollection $objOrder)
    {
        $arrData = array();

        $arrData['ACCOUNTID']   = $this->saferpay_accountid;
        $arrData['AMOUNT']      = round($objOrder->getTotal() * 100, 0);
        $arrData['CURRENCY']    = $objOrder->getCurrency();
        $arrData['SUCCESSLINK'] = \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder);
        $arrData['FAILLINK']    = \Environment::get('base') . Checkout::generateUrlForStep('failed');
        $arrData['BACKLINK']    = $arrData['FAILLINK'];
        $arrData['NOTIFYURL']   = \Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $arrData['DESCRIPTION'] = $this->saferpay_description;
        $arrData['ORDERID']     = $objOrder->getId();

        // Additional attributes
        if ($this->saferpay_vtconfig) {
            $arrData['VTCONFIG'] = $this->saferpay_vtconfig;
        }

        if ($this->saferpay_paymentmethods != '') {
            $arrData['PAYMENTMETHODS'] = $this->saferpay_paymentmethods;
        }

        return $arrData;
    }

    /**
     * Get data from POST
     */
    protected function getPostData()
    {
        // Cannot use \Input::post() here because it would kill XML data
        return $_POST['DATA'];
    }

    /**
     * Parse POST data XML and get attribute value
     *
     * @param string $strKey
     *
     * @return string
     */
    protected function getPostValue($strKey)
    {
        if (null === $this->objXML) {
            $doc = new \DOMDocument();
            $doc->loadXML($this->getPostData());
            $this->objXML = $doc->getElementsByTagName('IDP')->item(0)->attributes;
        }

        return (string) $this->objXML->getNamedItem($strKey)->nodeValue;
    }

    /**
     * Send a PayComplete request to the Saferpay terminal
     *
     * @param string $strId
     * @param string $strToken
     * @param bool   $blnCancel
     *
     * @return bool
     */
    protected function sendPayComplete($strId, $strToken, $blnCancel = false)
    {
        $params = array(
            'ID'          => $strId,
            'ACCOUNTID'   => $this->saferpay_accountid,
            'ACTION'      => $blnCancel ? 'Cancel' : 'Settlement',
            'TOKEN'       => $strToken
        );

        // This is only for the sandbox mode where a password is required
        if ($this->debug) {
            $params['spPassword'] = '8e7Yn5yk';
        }

        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $objRequest->send($this->getApiUrl('PayCompleteV2.asp'), http_build_query($params, null, '&'), 'POST');

        // Stop if capture was not successful
        if ($objRequest->hasError() || 0 !== strpos(strtoupper($objRequest->response), 'OK:')) {
            \System::log(sprintf('Saferpay PayComplete failed. See log files for further details.'), __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('Saferpay PayComplete failed. Message was: "%s".', $objRequest->response));

            return false;
        }

        return true;
    }

    /**
     * Check XML data, add to log if debugging is enabled
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return bool
     */
    private function validateXML(IsotopeProductCollection $objOrder)
    {
        if ($this->getPostValue('ACCOUNTID') != $this->saferpay_accountid) {
            \System::log('XML data wrong, possible manipulation (accountId validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('ACCOUNTID'), $this->saferpay_accountid));

            return false;
        }

        if ($this->getPostValue('AMOUNT') != round($objOrder->getTotal() * 100, 0)) {
            \System::log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('AMOUNT'), $objOrder->getTotal()));

            return false;
        }

        if ($this->getPostValue('CURRENCY') !== $objOrder->getCurrency()) {
            \System::log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('XML data wrong, possible manipulation (currency validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('CURRENCY'), $this->currency));

            return false;
        }

        return true;
    }

    /**
     * Returns the base URL for Saferpay API depending on test or production mode.
     *
     * @param string $script
     *
     * @return string
     */
    private function getApiUrl($script)
    {
        if ($this->debug) {
            return 'https://test.saferpay.com/hosting/'.$script;
        }

        return 'https://www.saferpay.com/hosting/'.$script;
    }
}
