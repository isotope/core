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

use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\Module;
use Contao\Request;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopeOrderStatusAware;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saferpay payment method
 *
 * @property string $saferpay_accountid
 * @property string $saferpay_username
 * @property string $saferpay_password
 * @property string $saferpay_description
 * @property string $saferpay_vtconfig
 * @property string $saferpay_paymentmethods
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
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return;
        }

        $arrPayment = StringUtil::deserialize($objOrder->payment_data, true);

        if (!$this->saferpay_username) {
            // TODO: remove once HTTPS interface is no longer supported

            if (!$this->validateXML($objOrder)) {
                return;
            }

            // Get the Payment URL from the saferpay hosting server
            $objRequest = new Request();
            $objRequest->send($this->getApiUrl('VerifyPayConfirm.asp') . "?DATA=" . urlencode($this->getPostData()) . "&SIGNATURE=" . urlencode(Input::post('SIGNATURE')));

            // Stop if verification is not working
            if (0 !== strpos(strtoupper($objRequest->response), 'OK:')) {
                System::log(sprintf('Payment not successfull. See log files for further details.'), __METHOD__, TL_ERROR);
                $this->debugLog(sprintf('Payment not successfull. Message was: "%s".', $objRequest->response));

                return;
            }

            $arrResponse = array();
            parse_str(substr($objRequest->response, 3), $arrResponse);

            $arrPayment['POSTSALE'][] = $this->getPostData();
            $arrPayment['PAYCONFIRM'] = $arrResponse;

        } else {

            try {
                $json = $this->sendJsonRequest(
                    '/Payment/v1/PaymentPage/Assert',
                    ['Token' => $arrPayment['PAYCONFIRM']['TOKEN']]
                );

                if (!isset($json['Transaction']['Id'])
                    || $json['Transaction']['Type'] !== 'PAYMENT'
                    || $json['Transaction']['Amount']['Value'] != round($objOrder->getTotal() * 100)
                    || $json['Transaction']['Amount']['CurrencyCode'] !== $objOrder->getCurrency()
                ) {
                    System::log('Saferpay assertion failed, possible order manipulation! See log files for further details.', __METHOD__, TL_ERROR);
                    $this->debugLog(sprintf("Saferpay assertion failed, possible manipulation for order ID %s! JSON Response: %s", $objOrder->getId(), print_r($json, true)));

                    return;
                }

                $arrPayment['POSTSALE'][] = $json;
                $arrPayment['PAYCONFIRM']['ID'] = $json['Transaction']['Id'];

            } catch (\RuntimeException $e) {
                return;
            }
        }

        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->payment_data = $arrPayment;
        $objOrder->save();

        // everything has been okay so far and the debit has been authorized. We capture it now if this is requested (usually it is).
        if ('auth' !== $this->trans_type
            && $this->sendPayComplete($arrPayment['PAYCONFIRM']['ID'], $arrPayment['PAYCONFIRM']['TOKEN'])
        ) {
            $objOrder->setDatePaid(time());
        }

        $objOrder->updateOrderStatus($this->new_order_status);
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        $orderId = (int) Input::get('orderid');

        if (!$orderId) {
            $orderId = (int) $this->getPostValue('ORDERID');
        }

        return Order::findByPk($orderId);
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        $redirectUrl = $this->initializePaymentPage($objOrder);

        if (null === $redirectUrl) {
            Checkout::redirectToStep('failed');
            return '';
        }

        $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="1; URL=' . $redirectUrl . '">';

        return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<p><a href="' . $redirectUrl . '">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2] . '</a></p>';
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

            $arrPayment = StringUtil::deserialize($objOrder->payment_data, true);
            $blnResult = $this->sendPayComplete($arrPayment['PAYCONFIRM']['ID'], $arrPayment['PAYCONFIRM']['TOKEN']);

            if ('BE' === TL_MODE) {
                if ($blnResult) {
                    Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusSuccess']);
                } else {
                    Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusError']);
                }
            }

        } elseif ('cancel' === $objNewStatus->saferpay_status && 'BE' === TL_MODE) {
            Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusCancel']);
        }
    }

    /**
     * Generate POST data to initialize payment
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return array
     * @deprecated
     */
    protected function generatePaymentPostData(IsotopeProductCollection $objOrder)
    {
        $arrData = array();

        $arrData['ACCOUNTID']   = $this->saferpay_accountid;
        $arrData['AMOUNT']      = round($objOrder->getTotal() * 100);
        $arrData['CURRENCY']    = $objOrder->getCurrency();
        $arrData['SUCCESSLINK'] = Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder);
        $arrData['FAILLINK']    = Environment::get('base') . Checkout::generateUrlForStep('failed');
        $arrData['BACKLINK']    = $arrData['FAILLINK'];
        $arrData['NOTIFYURL']   = Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id='.$this->id;
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

    protected function generatePaymentJsonData(IsotopeProductCollection $objOrder)
    {
        [, $terminalId] = explode('-', $this->saferpay_accountid, 2);
        $failedUrl = Checkout::generateUrlForStep('failed');

        $data = [
            'TerminalId' => $terminalId,
            'Payment' => [
                'Amount' => [
                    'Value' => round($objOrder->getTotal() * 100),
                    'CurrencyCode' => $objOrder->getCurrency(),
                ],
                'OrderId' => $objOrder->getId(),
                'Description' => $this->saferpay_description,
            ],
            'Payer' => [
                'LanguageCode' => substr($GLOBALS['TL_LANGUAGE'], 0, 2),
            ],
            'ReturnUrls' => [
                'Success' => Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder),
                'Fail' => Environment::get('base') . $failedUrl,
                'Abort' => Environment::get('base') . $failedUrl,
            ],
            'Notification' => [
                'SuccessNotifyUrl' => Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id.'&orderid='.$objOrder->getId(),
                'FailNotifyUrl' => Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id.'&orderid='.$objOrder->getId(),
            ],
        ];

        if ($this->saferpay_vtconfig) {
            $data['ConfigSet'] = $this->saferpay_vtconfig;
        }

        if (!empty($this->saferpay_paymentmethods)) {
            $data['PaymentMethods'] = explode(',', $this->saferpay_paymentmethods);
        }

        return $data;
    }

    /**
     * Send a PayComplete request to the Saferpay terminal
     *
     * @param string      $strId
     * @param string|null $strToken
     * @param bool        $blnCancel
     *
     * @return bool
     */
    protected function sendPayComplete($strId, $strToken = null, $blnCancel = false)
    {
        if (!$this->saferpay_username) {
            @trigger_error('The Saferpay HTTPS interface will be discontinued soon! Please switch to the JSON API asap.', E_USER_DEPRECATED);

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

            $objRequest = new Request();
            $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $objRequest->send($this->getApiUrl('PayCompleteV2.asp'), http_build_query($params, null, '&'), 'POST');

            // Stop if capture was not successful
            if ($objRequest->hasError() || 0 !== strpos(strtoupper($objRequest->response), 'OK:')) {
                System::log(sprintf('Saferpay PayComplete failed. See log files for further details.'), __METHOD__, TL_ERROR);
                $this->debugLog(sprintf('Saferpay PayComplete failed. Message was: "%s".', $objRequest->response));

                return false;
            }

            return true;
        }

        try {
            $json = $this->sendJsonRequest(
                '/Payment/v1/Transaction/Capture',
                [
                    'TransactionReference' => ['TransactionId' => $strId]
                ]
            );

            return $json['Status'] === 'CAPTURED';

        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Check XML data, add to log if debugging is enabled
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return bool
     * @deprecated
     */
    private function validateXML(IsotopeProductCollection $objOrder)
    {
        @trigger_error('The Saferpay HTTPS interface will be discontinued soon! Please switch to the JSON API asap.', E_USER_DEPRECATED);

        if ($this->getPostValue('ACCOUNTID') != $this->saferpay_accountid) {
            System::log('XML data wrong, possible manipulation (accountId validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('XML data wrong, possible manipulation (accountId validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('ACCOUNTID'), $this->saferpay_accountid));

            return false;
        }

        if ($this->getPostValue('AMOUNT') != round($objOrder->getTotal() * 100)) {
            System::log('XML data wrong, possible manipulation (amount validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('XML data wrong, possible manipulation (amount validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('AMOUNT'), $objOrder->getTotal()));

            return false;
        }

        if ($this->getPostValue('CURRENCY') !== $objOrder->getCurrency()) {
            System::log('XML data wrong, possible manipulation (currency validation failed)! See log files for further details.', __METHOD__, TL_ERROR);
            $this->debugLog(sprintf('XML data wrong, possible manipulation (currency validation failed)! XML was: "%s". Order was: "%s"', $this->getPostValue('CURRENCY'), $this->currency));

            return false;
        }

        return true;
    }

    /**
     * @return string|null
     */
    private function initializePaymentPage(IsotopeProductCollection $objOrder)
    {
        if (!$this->saferpay_username) {
            @trigger_error('The Saferpay HTTPS interface will be discontinued soon! Please switch to the JSON API asap.', E_USER_DEPRECATED);

            $objRequest = new Request();
            $objRequest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $objRequest->send($this->getApiUrl('CreatePayInit.asp'), http_build_query($this->generatePaymentPostData($objOrder), null, '&'), 'POST');

            if ((int) $objRequest->code !== 200 || 0 === strpos($objRequest->response, 'ERROR:')) {
                System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
                $this->debugLog(sprintf('Could not get the redirect URI from Saferpay. Response was: "%s".', $objRequest->response));

                return null;
            }

            return $objRequest->response;
        }

        try {
            $json = $this->sendJsonRequest(
                '/Payment/v1/PaymentPage/Initialize',
                $this->generatePaymentJsonData($objOrder)
            );

            if (!isset($json['RedirectUrl'], $json['Token'])) {
                System::log(sprintf('Could not get the redirect URI from Saferpay. See log files for further details.'), __METHOD__, TL_ERROR);
                $this->debugLog(sprintf("Could not get the redirect URI from Saferpay. JSON Response:\n%s", print_r($json, true)));
            }

            $paymentData = StringUtil::deserialize($objOrder->payment_data, true);
            $paymentData['PAYCONFIRM']['TOKEN'] = $json['Token'];

            $objOrder->payment_data = $paymentData;
            $objOrder->save();

            return $json['RedirectUrl'];

        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * @param string     $script
     * @param array|null $data
     *
     * @return array
     */
    private function sendJsonRequest($script, array $data)
    {
        [$customerId] = explode('-', $this->saferpay_accountid, 2);

        $data = array_replace(
            [
                'RequestHeader' => [
                    'SpecVersion' => '1.10',
                    'CustomerId' => $customerId,
                    'RequestId' => uniqid('', false),
                    'RetryIndicator' => 0,
                ],
            ],
            $data
        );

        $objRequest = new Request();
        $objRequest->setHeader('Accept', 'application/json');
        $objRequest->setHeader('Content-Type', 'application/json; charset=utf-8');

        $objRequest->send($this->getApiUrl($script), json_encode($data), 'POST');

        if ($objRequest->code !== 200) {
            System::log(sprintf('Saferpay request failed with error %s. See log files for further details.', $objRequest->code), __METHOD__, TL_ERROR);
            $this->debugLog(sprintf(
                "Saferpay request failed with error %s.\n\nRequest:%s\n\nResponse:\n%s.",
                $objRequest->code,
                $objRequest->request,
                $objRequest->response
            ));

            throw new \RuntimeException('Saferpay request failed', $objRequest->code);
        }

        $json = @json_decode($objRequest->response, true);

        if (!\is_array($json)) {
            throw new \RuntimeException('Saferpay response contained invalid JSON', $objRequest->code);
        }

        return $json;
    }

    /**
     * Returns the base URL for Saferpay HTTP or JSON API depending on test or production mode.
     *
     * @param string $script
     *
     * @return string
     */
    private function getApiUrl($script)
    {
        if (!$this->saferpay_username) {
            @trigger_error('The Saferpay HTTPS interface will be discontinued soon! Please switch to the JSON API asap.', E_USER_DEPRECATED);

            return sprintf(
                'https://%s.saferpay.com/hosting/%s',
                $this->debug ? 'test' : 'www',
                $script
            );
        }

        return sprintf(
            'https://%s:%s@%s.saferpay.com/api/%s',
            $this->saferpay_username,
            $this->saferpay_password,
            $this->debug ? 'test' : 'www',
            ltrim($script, '/')
        );
    }

    /**
     * Get data from POST
     * @deprecated
     */
    protected function getPostData()
    {
        // Cannot use Input::post() here because it would kill XML data
        return $_POST['DATA'];
    }

    /**
     * Parse POST data XML and get attribute value
     *
     * @param string $strKey
     *
     * @return string
     * @deprecated
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
}
