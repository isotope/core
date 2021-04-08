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

use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;

/**
 * Datatrans payment method
 *
 * @property string $datatrans_id
 * @property string $datatrans_sign
 * @property string $datatrans_hash_method
 * @property string $datatrans_hash_convert
 */
class Datatrans extends Postsale
{
    /**
     * Perform server to server data check
     *
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        // Verify payment status
        if (\Input::post('status') !== 'success') {
            \System::log('Payment for order ID "' . \Input::post('refno') . '" failed.', __METHOD__, TL_ERROR);
            $this->debugLog('Expected status "success", got "'.\Input::post('status').'"');
            $this->debugLog($_POST);

            return;
        }

        // Validate HMAC sign
        $hash = $this->createHash($this->datatrans_id . \Input::post('amount') . \Input::post('currency') . \Input::post('uppTransactionId'));

        if (\Input::post('sign2') != $hash) {
            \System::log('Invalid HMAC signature for Order ID ' . \Input::post('refno'), __METHOD__, TL_ERROR);
            $this->debugLog('Expected hash "'.$hash.'", got "'.\Input::post('sign2').'"');
            $this->debugLog($_POST);

            return;
        }

        // For maximum security, also validate individual parameters
        if (!$this->validateParameters(
            [
                'refno'    => $objOrder->getId(),
                'currency' => $objOrder->getCurrency(),
                'amount'   => round($objOrder->getTotal() * 100),
                'reqtype'  => 'auth' === $this->trans_type ? 'NOA' : 'CAA',
            ]
        )) {
            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            \System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return;
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('refno') . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('refno'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $objAddress = $objOrder->getBillingAddress();

        $arrParams = array
        (
            'merchantId'            => $this->datatrans_id,
            'amount'                => round($objOrder->getTotal() * 100),
            'currency'              => $objOrder->getCurrency(),
            'refno'                 => $objOrder->getId(),
            'language'              => $objOrder->language,
            'reqtype'               => 'auth' === $this->trans_type ? 'NOA' : 'CAA',
            'uppCustomerDetails'    => 'yes',
            'uppCustomerTitle'      => $objAddress->salutation,
            'uppCustomerFirstName'  => $objAddress->firstname,
            'uppCustomerLastName'   => $objAddress->lastname,
            'uppCustomerStreet'     => $objAddress->street_1,
            'uppCustomerStreet2'    => $objAddress->street_2,
            'uppCustomerCity'       => $objAddress->city,
            'uppCustomerCountry'    => $objAddress->country,
            'uppCustomerZipCode'    => $objAddress->postal,
            'uppCustomerPhone'      => $objAddress->phone,
            'uppCustomerEmail'      => $objAddress->email,
            'successUrl'            => ampersand(\Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder)),
            'errorUrl'              => ampersand(\Environment::get('base') . Checkout::generateUrlForStep('failed')),
            'cancelUrl'             => ampersand(\Environment::get('base') . Checkout::generateUrlForStep('failed')),
            'mod'                   => 'pay',
            'id'                    => $this->id,
        );

        // Security signature (see Security Level 2)
        $arrParams['sign'] = $this->createHash($arrParams['merchantId'] . $arrParams['amount'] . $arrParams['currency'] . $arrParams['refno']);

        $objTemplate           = new \Isotope\Template('iso_payment_datatrans');
        $objTemplate->id       = $this->id;
        $objTemplate->action   = ('https://' . ($this->debug ? 'pay.sandbox' : 'pay') . '.datatrans.com/upp/jsp/upStart.jsp');
        $objTemplate->params   = $arrParams;
        $objTemplate->headline = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }


    /**
     * Validate array of post parameter agains required values
     * @param array
     * @return boolean
     */
    private function validateParameters(array $arrData)
    {
        foreach ($arrData as $key => $value) {
            if (\Input::post($key) != $value) {
                \System::log(
                    'Wrong data for parameter "' . $key . '" (Order ID "' . \Input::post('refno') . ').',
                    __METHOD__,
                    TL_ERROR
                );

                $this->debugLog('Failed to validate parameters');
                $this->debugLog($arrData);
                $this->debugLog($_POST);

                return false;
            }
        }

        return true;
    }

    /**
     * Create hash based on module config for given value.
     *
     * @param string $value
     *
     * @return string
     */
    private function createHash($value)
    {
        $algo = 'sha256' === $this->datatrans_hash_method ? 'sha256' : 'md5';

        return hash_hmac(
            $algo,
            $value,
            $this->datatrans_hash_convert ? hex2bin($this->datatrans_sign) : $this->datatrans_sign
        );
    }
}
