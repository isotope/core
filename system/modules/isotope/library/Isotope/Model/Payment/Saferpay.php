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
    /**
     * CreatePayInit URI
     * @var string
     */
    const createPayInitURI = 'https://www.saferpay.com/hosting/CreatePayInit.asp';

    /**
     * VerifyPayConfirm URI
     * @var string
     */
    const verifyPayConfirmURI = 'https://www.saferpay.com/hosting/VerifyPayConfirm.asp';

    /**
     * PayCompleteURI
     * @var string
     */
    const payCompleteURI = 'https://www.saferpay.com/hosting/PayCompleteV2.asp';


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
        $objRequest->send(static::verifyPayConfirmURI . "?DATA=" . urlencode($this->getPostData()) . "&SIGNATURE=" . urlencode(\Input::post('SIGNATURE')));

        // Stop if verification is not working
        if (0 !== strpos(strtoupper($objRequest->response), 'OK:')) {
            \System::log(sprintf('Payment not successfull. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Payment not successfull. Message was: "%s".', $objRequest->response), 'isotope_saferpay.log');

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
        $objRequest->send(static::createPayInitURI, http_build_query($this->generatePaymentPostData($objOrder), null, '&'), 'POST');

        if ((int) $objRequest->code !== 200 || 0 !== strpos($objRequest->response, 'ERROR:')) {
            \System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response), 'isotope_saferpay.log');

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
        $arrData['BACKLINK'] = $arrData['FAILLINK'];
        $arrData['NOTIFYURL'] = \Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
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
     * @todo remove magic_quotes:gpc when PHP 5.4 is compulsory (it's also deprecated in PHP 5.3 so it might also be removed when PHP 5.3 is compulsory)
     */
    protected function getPostData()
    {
        // Cannot use \Input::post() here because it would kill XML data
        $strData = $_POST['DATA'];

        // catch magic_quotes_gpc is set to yes in php.ini (can be removed when PHP 5.4 is compulsory)
        if (0 === strpos($strData, '<IDP MSGTYPE=\"')) {
            $strData = stripslashes($strData);
        }

        return $strData;
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
        if (0 === strpos($this->saferpay_accountid, '99867-')) {
            $params['spPassword'] = 'XAjc3Kna';
        }

        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $objRequest->send(static::payCompleteURI, http_build_query($params, null, '&'), 'POST');

        // Stop if capture was not successful
        if ($objRequest->hasError() || 0 !== strpos(strtoupper($objRequest->response), 'OK:')) {
            \System::log(sprintf('Saferpay PayComplete failed. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Saferpay PayComplete failed. Message was: "%s".', $objRequest->response), 'isotope_saferpay.log');

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
            log_message(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('ACCOUNTID'), $this->saferpay_accountid), 'isotope_saferpay.log');

            return false;

        } elseif ($this->getPostValue('AMOUNT') != round($objOrder->getTotal() * 100, 0)) {
            \System::log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('AMOUNT'), $this->getTotal()), 'isotope_saferpay.log');

            return false;

        } elseif ($this->getPostValue('CURRENCY') !== $objOrder->getCurrency()) {
            \System::log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (currency validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('CURRENCY'), $this->currency), 'isotope_saferpay.log');

            return false;
        }

        return true;
    }
}
