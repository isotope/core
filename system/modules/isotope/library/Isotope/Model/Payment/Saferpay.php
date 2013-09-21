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
use Isotope\Model\ProductCollection\Order;


/**
 * Class PaymentSaferpay
 * @todo    remove magic_quotes:gpc when PHP 5.4 is compulsory (it's also deprecated in PHP 5.3 so it might also be removed when PHP 5.3 is compulsory)
 */
class Saferpay extends Postsale implements IsotopePayment
{

    /**
     * Version
     * @var string
     */
    const version = '2.0.0';

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
    const payCompleteURI = 'https://www.saferpay.com/hosting/PayComplete.asp';


    /**
     * Process Saferpay server to server notification
     */
    public function processPostsale()
    {
        if (\Input::get('mod') != 'pay' || \Input::get('id') != $this->id) {

            return false;
        }

        // Cannot use \Input::post() here because it would kill XML data
        $strData = $_POST['DATA'];

        // catch magic_quotes_gpc is set to yes in php.ini (can be removed when PHP 5.4 is compulsory)
        if (substr($strData, 0, 15) == '<IDP MSGTYPE=\\') {
            $strData = stripslashes($strData);
        }

        $doc = new DOMDocument();
        $doc->loadXML($strData);
        $attributes = $doc->getElementsByTagName('IDP')->item(0)->attributes;

        // validate the data on our side
        if (($objOrder = Order::findByPk($attributes->getNamedItem('ORDERID')->nodeValue)) === null) {
            \System::log(sprintf('Order ID could not be found. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Order ID could not be found. Order ID was: "%s".', $attributes->getNamedItem('ORDERID')->nodeValue), 'error.log');

            return;
        }

        if (!$this->validateXML($attributes, $objOrder)) {

            return;
        }

        // Get the Payment URL from the saferpay hosting server
        $objRequest = new \Request();
        $objRequest->send(static::verifyPayConfirmURI . "?DATA=" . urlencode($strData) . "&SIGNATURE=" . urlencode(\Input::post('SIGNATURE')));

        // Stop if verification is not working
        if (strtoupper(substr($objRequest->response, 0, 3)) != 'OK:')
        {
            \System::log(sprintf('Payment not successfull. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Payment not successfull. Message was: "%s".', $objRequest->response), 'error.log');

            return;
        }

        // everything has been okay so far and the debit has been authorized. We capture it now if this is requested (usually it is).
        if ($this->trans_type != 'auth') {

            $arrResponse = array();
            parse_str(substr($objRequest->response, 3), $arrResponse);

            $strUrl  = static::payCompleteURI . '?ACCOUNTID=' . $this->saferpay_accountid . '&ID=' . urlencode($arrResponse['ID']) . '&TOKEN=' . urlencode($arrResponse['TOKEN']);

            // This is only for the sandbox mode where a password is required
            if (substr($this->saferpay_accountid, 0, 6) == '99867-') {
                $strUrl .= '&spPassword=XAjc3Kna';
            }

            $objRequest = new \Request();
            $objRequest->send($strUrl);

            // Stop if capture was not successful
            if (strtoupper($objRequest->response) != 'OK') {
                \System::log(sprintf('Payment capture failed. See log files for further details.'), __METHOD__, TL_ERROR);
                log_message(sprintf('Payment capture failed. Message was: "%s".', $objRequest->response), 'error.log');

                return;
            }


            // otherwise checkout
            if (!$objOrder->checkout()) {
                \System::log('Postsale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                return;
            }

            $objOrder->date_paid = time();
            $objOrder->updateOrderStatus($this->new_order_status);

            $objOrder->save();
        }
    }


    /**
     * HTML form for checkout
     *
     * @access public
     * @return mixed
     */
    public function checkoutForm()
    {
        // Get redirect url
        $objRequest = new \Request();
        $objRequest->send($this->createPaymentURI());

        if ((int) $objRequest->code !== 200 || substr($objRequest->response, 0, 6) === 'ERROR:') {
            \System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response), 'error.log');

            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="1; URL=' . $objRequest->response . '">';

        return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<p><a href="' . $objRequest->response . '">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]. '</a></p>';
    }


    /**
     * Check XML data, add to log if debugging is enabled
     *
     * @param  DOMNamedNodeMap
     * @param  Order
     * @return bool
     */
    private function validateXML($attributes, Order $objOrder)
    {
        log_message(print_r($objOrder, true), 'postsale.log');
        if ($attributes->getNamedItem('ACCOUNTID')->nodeValue != $this->saferpay_accountid) {
            \System::log('XML data wrong, possible manipulation (accountId validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $attributes->getNamedItem('ACCOUNTID')->nodeValue, $this->saferpay_accountid), 'error.log');

            return false;

        } elseif ($attributes->getNamedItem('AMOUNT')->nodeValue != round(($objOrder->grandTotal * 100), 0)) {
            \System::log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $attributes->getNamedItem('AMOUNT')->nodeValue, $this->grandTotal), 'error.log');

            return false;

        } elseif ($attributes->getNamedItem('CURRENCY')->nodeValue != $objOrder->currency) {
            \System::log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (currency validation failed)! XML was: "%s". Order was: "%s"', $attributes->getNamedItem('CURRENCY')->nodeValue, $this->currency), 'error.log');

            return false;
        }

        return true;
    }


    /**
     * Create payment URI
     * @return string
     */
    private function createPaymentURI()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null) {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $strComplete = \Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('complete') . '?uid=' . $objOrder->uniqid;
        $strFailed = \Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed');

        $strUrl  = static::createPayInitURI;
        $strUrl .= "?ACCOUNTID=" . $this->saferpay_accountid;
        $strUrl .= "&AMOUNT=" . (round((Isotope::getCart()->getTotal() * 100), 0));
        $strUrl .= "&CURRENCY=" . Isotope::getConfig()->currency;
        $strUrl .= "&SUCCESSLINK=" . urlencode($strComplete);
        $strUrl .= "&FAILLINK=" . urlencode($strFailed);
        $strUrl .= "&BACKLINK=" . urlencode($strFailed);
        $strUrl .= "&NOTIFYURL=" . urlencode(\Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id);
        $strUrl .= "&DESCRIPTION=" . urlencode($this->saferpay_description);
        $strUrl .= "&ORDERID=" . $objOrder->id; // order id

        // Additional attributes
        if ($this->saferpay_vtconfig) {
            $strUrl .= '&VTCONFIG=' . urlencode($this->saferpay_vtconfig);
        }

        return $strUrl;
    }
}
