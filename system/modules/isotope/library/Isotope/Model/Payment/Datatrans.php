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

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Order;


/**
 * Class Datatrans
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Leo Unglaub <leo@leo-unglaub.net>
 */
class Datatrans extends Postsale implements IsotopePayment
{

    /**
     * Perform server to server data check
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        // Verify payment status
        if (\Input::post('status') != 'success') {
            \System::log('Payment for order ID "' . \Input::post('refno') . '" failed.', __METHOD__, TL_ERROR);

            return false;
        }

        // Validate HMAC sign
        if (\Input::post('sign2') != hash_hmac('md5', $this->datatrans_id . \Input::post('amount') . \Input::post('currency') . \Input::post('uppTransactionId'), $this->datatrans_sign)) {
            \System::log('Invalid HMAC signature for Order ID ' . \Input::post('refno'), __METHOD__, TL_ERROR);

            return false;
        }

        // For maximum security, also validate individual parameters
        if (!$this->validateParameters(array(
            'refno'         => $objOrder->id,
            'currency'      => $objOrder->currency,
            'amount'        => round($objOrder->getTotal() * 100),
            'reqtype'       => ($this->trans_type == 'auth' ? 'NOA' : 'CAA'),
        )))
        {
            return false;
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('refno') . '" failed', __METHOD__, TL_ERROR);

            return false;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('refno'));
    }

    /**
     * Generate the submit form for datatrans and if javascript is enabled redirect automaticly
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $objAddress = $objOrder->getBillingAddress();

        $arrParams = array
        (
            'merchantId'            => $this->datatrans_id,
            'amount'                => round($objOrder->getTotal() * 100),
            'currency'              => $objOrder->currency,
            'refno'                 => $objOrder->id,
            'language'              => $objOrder->language,
            'reqtype'               => ($this->trans_type == 'auth' ? 'NOA' : 'CAA'),
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
            'successUrl'            => ampersand(\Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder)),
            'errorUrl'              => ampersand(\Environment::get('base') . $objModule->generateUrlForStep('failed')),
            'cancelUrl'             => ampersand(\Environment::get('base') . $objModule->generateUrlForStep('failed')),
            'mod'                   => 'pay',
            'id'                    => $this->id,
        );

        // Security signature (see Security Level 2)
        $arrParams['sign'] = hash_hmac('md5', $arrParams['merchantId'] . $arrParams['amount'] . $arrParams['currency'] . $arrParams['refno'], $this->datatrans_sign);

        $objTemplate           = new \Isotope\Template('iso_payment_datatrans');
        $objTemplate->id       = $this->id;
        $objTemplate->action   = ('https://' . ($this->debug ? 'pilot' : 'payment') . '.datatrans.biz/upp/jsp/upStart.jsp');
        $objTemplate->params   = $arrParams;
        $objTemplate->headline = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

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
                \System::log('Wrong data for parameter "' . $key . '" (Order ID "' . \Input::post('refno') . ').', __METHOD__, TL_ERROR);

                return false;
            }
        }

        return true;
    }
}
