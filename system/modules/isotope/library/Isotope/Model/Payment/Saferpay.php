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
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;


class Saferpay extends Postsale implements IsotopePayment, IsotopeOrderStatusAware
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
     * Process Saferpay server to server notification
     *
     * @param IsotopeProductCollection $objOrder
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$this->validateXML($objOrder)) {
            return;
        }

        // Get the Payment URL from the saferpay hosting server
        $objRequest = new \Request();
        $objRequest->send(static::verifyPayConfirmURI . "?DATA=" . urlencode($this->getPostData()) . "&SIGNATURE=" . urlencode(\Input::post('SIGNATURE')));

        // Stop if verification is not working
        if (strtoupper(substr($objRequest->response, 0, 3)) != 'OK:') {
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
            \System::log('Postsale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->payment_data = $arrPayment;
        $objOrder->save();

        // everything has been okay so far and the debit has been authorized. We capture it now if this is requested (usually it is).
        if ($this->trans_type != 'auth') {
            $this->sendPayComplete($arrPayment['PAYCONFIRM']['ID'], $arrPayment['PAYCONFIRM']['TOKEN']);
            $objOrder->date_paid = time();
        }

        $objOrder->updateOrderStatus($this->new_order_status);
    }

    /**
     * Get the order object in a postsale request
     *
     * @return IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk($this->getPostValue('ORDERID'));
    }

    /**
     * HTML form for checkout
     *
     * @param IsotopeProductCollection $objOrder  The order being places
     * @param \Module                  $objModule The checkout module instance
     *
     * @return mixed
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        // Get redirect url
        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $objRequest->send(static::createPayInitURI, http_build_query($this->generatePaymentPostData($objOrder, $objModule), null, '&'), 'POST');

        if ((int) $objRequest->code !== 200 || substr($objRequest->response, 0, 6) === 'ERROR:') {
            \System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response), 'isotope_saferpay.log');

            $objModule->redirectToStep('failed');
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
        $blnCancel = null;

        if ($objNewStatus->saferpay_status == 'capture') {
            $blnCancel = false;
        } elseif ($objNewStatus->saferpay_status == 'cancel') {
            $blnCancel = true;
        }

        if (null !== $blnCancel) {
            $arrPayment = deserialize($objOrder->payment_data, true);
            $blnResult = $this->sendPayComplete($arrPayment['PAYCONFIRM']['ID'], $arrPayment['PAYCONFIRM']['TOKEN'], $blnCancel);

            if (TL_MODE == 'BE') {
                if ($blnResult) {
                    \Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusSuccess']);
                } else {
                    \Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusError']);
                }
            }
        }
    }

    /**
     * Generate POST data to initialize payment
     *
     * @param IsotopeProductCollection $objOrder
     * @param \Module                  $objModule
     *
     * @return array
     */
    protected function generatePaymentPostData(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $arrData = array();

        $arrData['ACCOUNTID'] = $this->saferpay_accountid;
        $arrData['AMOUNT'] = (round(($objOrder->getTotal() * 100), 0));
        $arrData['CURRENCY'] = $objOrder->currency;
        $arrData['SUCCESSLINK'] = \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder);
        $arrData['FAILLINK'] = \Environment::get('base') . $objModule->generateUrlForStep('failed');
        $arrData['BACKLINK'] = $arrData['FAILLINK'];
        $arrData['NOTIFYURL'] = \Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $arrData['DESCRIPTION'] = $this->saferpay_description;
        $arrData['ORDERID'] = $objOrder->id; // order id

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
        if (substr($strData, 0, 15) == '<IDP MSGTYPE=\"') {
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
            'ACTION'      => ($blnCancel ? 'Cancel' : 'Settlement'),
            'TOKEN'       => $strToken
        );

        // This is only for the sandbox mode where a password is required
        if (substr($this->saferpay_accountid, 0, 6) == '99867-') {
            $params['spPassword'] = 'XAjc3Kna';
        }

        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $objRequest->send(static::payCompleteURI, http_build_query($params, null, '&'), 'POST');

        // Stop if capture was not successful
        if ($objRequest->hasError() || strtoupper(substr($objRequest->response, 0, 3)) != 'OK:') {
            \System::log(sprintf('Saferpay PayComplete failed. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Saferpay PayComplete failed. Message was: "%s".', $objRequest->response), 'isotope_saferpay.log');

            return false;
        }

        return true;
    }


    /**
     * Check XML data, add to log if debugging is enabled
     *
     * @param Order $objOrder
     *
     * @return bool
     */
    private function validateXML(Order $objOrder)
    {
        if ($this->getPostValue('ACCOUNTID') != $this->saferpay_accountid) {
            \System::log('XML data wrong, possible manipulation (accountId validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('ACCOUNTID'), $this->saferpay_accountid), 'isotope_saferpay.log');

            return false;

        } elseif ($this->getPostValue('AMOUNT') != round(($objOrder->getTotal() * 100), 0)) {
            \System::log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('AMOUNT'), $this->getTotal()), 'isotope_saferpay.log');

            return false;

        } elseif ($this->getPostValue('CURRENCY') != $objOrder->currency) {
            \System::log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (currency validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('CURRENCY'), $this->currency), 'isotope_saferpay.log');

            return false;
        }

        return true;
    }
}
