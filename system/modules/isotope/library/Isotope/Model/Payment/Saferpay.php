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

use Isotope\Interfaces\IsotopePayment;
use Isotope\Model\Payment;


/**
 * Class PaymentSaferpay
 * @TODO: remove magic_quotes:gpc when PHP 5.4 is compulsory (it's also deprecated in PHP 5.3 so it might also be removed when PHP 5.3 is compulsory)
 */
class Saferpay extends Payment implements IsotopePayment
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
     * Return a list of status options.
     *
     * @access public
     * @return array
     */
    public function statusOptions()
    {
        return array('pending', 'processing', 'complete', 'on_hold');
    }


    /**
     * Process Saferpay server to server notification
     */
    public function processPostSale()
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
        $objOrder = new IsotopeOrder();
        if (!$objOrder->findBy('id', $attributes->getNamedItem('ORDERID')->nodeValue)) {
            $this->log(sprintf('Order ID could not be found. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Order ID could not be found. Order ID was: "%s".', $attributes->getNamedItem('ORDERID')->nodeValue), 'error.log');

            return;
        }

        if (!$this->validateXML($attributes, $objOrder)) {

            return;
        }

        // Get the Payment URL from the saferpay hosting server
        $objRequest = new Request();
        $objRequest->send(static::verifyPayConfirmURI . "?DATA=" . urlencode($strData) . "&SIGNATURE=" . urlencode(\Input::post('SIGNATURE')));

        // Stop if verification is not working
        if (strtoupper(substr($objRequest->response, 0, 3)) != 'OK:')
        {
            $this->log(sprintf('Payment not successfull. See log files for further details.'), __METHOD__, TL_ERROR);
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

            $objRequest = new Request();
            $objRequest->send($strUrl);

            // Stop if capture was not successful
            if (strtoupper($objRequest->response) != 'OK')
            {
                $this->log(sprintf('Payment capture failed. See log files for further details.'), __METHOD__, TL_ERROR);
                log_message(sprintf('Payment capture failed. Message was: "%s".', $objRequest->response), 'error.log');

                return;
            }


            // otherwise checkout
            if (!$objOrder->checkout())
            {
                $this->log('Checkout for Saferpay failed.', __METHOD__, TL_ERROR);

                return;
            }

            $objOrder->date_paid = time();
            $objOrder->updateOrderStatus($this->new_order_status);
            $objOrder->save();
        }
    }


    /**
     * Process checkout payment.
     *
     * @access public
     * @return mixed
     */
    public function processPayment()
    {
        $objOrder = new IsotopeOrder();
        if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
        {
            return false;
        }

        if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
        {
            IsotopeFrontend::clearTimeout();
            return true;
        }

        if (IsotopeFrontend::setTimeout())
        {
            // Do not index or cache the page
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate = new FrontendTemplate('mod_message');
            $objTemplate->type = 'processing';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
            return $objTemplate->parse();
        }

        $this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
        $this->redirect($this->addToUrl('step=failed', true));
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
        $objRequest = new Request();
        $objRequest->send($this->createPaymentURI());

        if ((int) $objRequest->code !== 200 || substr($objRequest->response, 0, 6) === 'ERROR:') {
            $this->log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
            log_message(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response), 'error.log');
            $this->redirect($this->addToUrl('step=failed', true));
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
     * @param  IsotopeOrder
     * @return bool
     */
    private function validateXML($attributes, $objOrder)
    {
        log_message(print_r($objOrder, true), 'postsale.log');
        if ($attributes->getNamedItem('ACCOUNTID')->nodeValue != $this->saferpay_accountid) {
            $this->log('XML data wrong, possible manipulation (accountId validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $attributes->getNamedItem('ACCOUNTID')->nodeValue, $this->saferpay_accountid), 'error.log');

            return false;

        } elseif ($attributes->getNamedItem('AMOUNT')->nodeValue != round(($objOrder->grandTotal * 100), 0)) {
            $this->log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            log_message(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $attributes->getNamedItem('AMOUNT')->nodeValue, $this->grandTotal), 'error.log');

            return false;

        } elseif ($attributes->getNamedItem('CURRENCY')->nodeValue != $objOrder->currency) {
            $this->log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
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
        $objOrder = Database::getInstance()->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->execute($this->Isotope->Cart->id);

        $strComplete = $this->Environment->base . $this->addToUrl('step=complete', true) . '?uid=' . $objOrder->uniqid;
        $strFailed = $this->Environment->base . $this->addToUrl('step=failed', true);

        $strUrl  = static::createPayInitURI;
        $strUrl .= "?ACCOUNTID=" . $this->saferpay_accountid;
        $strUrl .= "&AMOUNT=" . (round(($this->Isotope->Cart->grandTotal * 100), 0));
        $strUrl .= "&CURRENCY=" . $this->Isotope->Config->currency;
        $strUrl .= "&SUCCESSLINK=" . urlencode($strComplete);
        $strUrl .= "&FAILLINK=" . urlencode($strFailed);
        $strUrl .= "&BACKLINK=" . urlencode($strFailed);
        $strUrl .= "&NOTIFYURL=" . urlencode($this->Environment->base . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id);
        $strUrl .= "&DESCRIPTION=" . urlencode($this->saferpay_description);
        $strUrl .= "&ORDERID=" . $objOrder->id; // order id

        // Additional attributes
        if ($this->saferpay_vtconfig) {
            $strUrl .= '&VTCONFIG=' . urlencode($this->saferpay_vtconfig);
        }

        return $strUrl;
    }
}
