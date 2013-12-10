<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;


/**
 * Class PaymentSaferpay
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


    protected $objXML;


    /**
     * Process Saferpay server to server notification
     * @param   IsotopeProductCollection
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
            log_message(sprintf('Payment not successfull. Message was: "%s".', $objRequest->response), 'error.log');

            return;
        }

        // everything has been okay so far and the debit has been authorized. We capture it now if this is requested (usually it is).
        if ($this->trans_type != 'auth') {

            $arrResponse = array();
            parse_str(substr($objRequest->response, 3), $arrResponse);

            $strUrl = static::payCompleteURI . '?ACCOUNTID=' . $this->saferpay_accountid . '&ID=' . urlencode($arrResponse['ID']) . '&TOKEN=' . urlencode($arrResponse['TOKEN']);

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
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk($this->getPostValue('ORDERID'));
    }

    /**
     * HTML form for checkout
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        // Get redirect url
        $objRequest = new \Request();
        $objRequest->send($this->createPaymentURI($objOrder, $objModule));

        if ((int) $objRequest->code !== 200 || substr($objRequest->response, 0, 6) === 'ERROR:') {
            \System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response), 'error.log');

            $objModule->redirectToStep('failed');
        }

        $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="1; URL=' . $objRequest->response . '">';

        return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<p><a href="' . $objRequest->response . '">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2] . '</a></p>';
    }

    /**
     * Get data from POST
     * @todo    remove magic_quotes:gpc when PHP 5.4 is compulsory (it's also deprecated in PHP 5.3 so it might also be removed when PHP 5.3 is compulsory)
     */
    private function getPostData()
    {
        // Cannot use \Input::post() here because it would kill XML data
        $strData = $_POST['DATA'];

        // catch magic_quotes_gpc is set to yes in php.ini (can be removed when PHP 5.4 is compulsory)
        if (substr($strData, 0, 15) == '<IDP MSGTYPE=\\') {
            $strData = stripslashes($strData);
        }

        return $strData;
    }

    /**
     * Parse POST data XML and get attribute value
     * @param   string
     * @return  string
     */
    private function getPostValue($strKey)
    {
        if (null === $this->objXML) {
            $doc = new DOMDocument();
            $doc->loadXML($this->getPostData());
            $this->objXML = $doc->getElementsByTagName('IDP')->item(0)->attributes;
        }

        return (string) $this->objXML->getNamedItem($strKey)->nodeValue;
    }


    /**
     * Check XML data, add to log if debugging is enabled
     *
     * @param  DOMNamedNodeMap
     * @param  Order
     * @return bool
     */
    private function validateXML(Order $objOrder)
    {
        if ($this->getPostValue('ACCOUNTID') != $this->saferpay_accountid) {
            \System::log('XML data wrong, possible manipulation (accountId validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('ACCOUNTID'), $this->saferpay_accountid), 'error.log');

            return false;

        } elseif ($this->getPostValue('AMOUNT') != round(($objOrder->getTotal() * 100), 0)) {
            \System::log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('AMOUNT'), $this->getTotal()), 'error.log');

            return false;

        } elseif ($this->getPostValue('CURRENCY') != $objOrder->currency) {
            \System::log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (currency validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('CURRENCY'), $this->currency), 'error.log');

            return false;
        }

        return true;
    }


    /**
     * Create payment URI
     * @return string
     */
    private function createPaymentURI(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $strComplete = \Environment::get('base') . $objModule->generateUrlForStep('complete') . '?uid=' . $objOrder->uniqid;
        $strFailed   = \Environment::get('base') . $objModule->generateUrlForStep('failed');

        $strUrl = static::createPayInitURI;
        $strUrl .= "?ACCOUNTID=" . $this->saferpay_accountid;
        $strUrl .= "&AMOUNT=" . (round(($objOrder->getTotal() * 100), 0));
        $strUrl .= "&CURRENCY=" . $objOrder->currency;
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
