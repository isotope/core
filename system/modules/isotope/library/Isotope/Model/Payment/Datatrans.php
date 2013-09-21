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
     */
    public function processPostsale()
    {
        // Verify payment status
        if (\Input::post('status') != 'success') {
            \System::log('Payment for order ID "' . \Input::post('refno') . '" failed.', __METHOD__, TL_ERROR);
            return false;
        }

        if (($objOrder = Order::findByPk(\Input::post('refno'))) === null) {
            \System::log('Order ID "' . \Input::post('refno') . '" not found', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate HMAC sign
        if (\Input::post('sign2') != hash_hmac('md5', $this->datatrans_id.\Input::post('amount').\Input::post('currency').\Input::post('uppTransactionId'), $this->datatrans_sign)) {
            \System::log('Invalid HMAC signature for Order ID ' . \Input::post('refno'), __METHOD__, TL_ERROR);
            return false;
        }

        // For maximum security, also validate individual parameters
        if (!$this->validateParameters(array(
            'refno'        => $objOrder->id,
            'currency'    => $objOrder->currency,
            'amount'    => round($objOrder->getTotal() * 100),
            'reqtype'    => ($this->trans_type == 'auth' ? 'NOA' : 'CAA'),
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
     * Generate the submit form for datatrans and if javascript is enabled redirect automaticly
     * @return string
     */
    public function checkoutForm()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null)
        {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $objAddress = Isotope::getCart()->getBillingAddress();

        $arrParams = array
        (
            'merchantId'            => $this->datatrans_id,
            'amount'                => round(Isotope::getCart()->getTotal() * 100),
            'currency'              => Isotope::getConfig()->currency,
            'refno'                 => $objOrder->id,
            'language'              => $GLOBALS['TL_LANGUAGE'],
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
            'successUrl'            => ampersand(\Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('complete')),
            'errorUrl'              => ampersand(\Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed')),
            'cancelUrl'             => ampersand(\Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed')),
            'mod'                   => 'pay',
            'id'                    => $this->id,
        );

        // Security signature (see Security Level 2)
        $arrParams['sign'] = hash_hmac('md5', $arrParams['merchantId'].$arrParams['amount'].$arrParams['currency'].$arrParams['refno'], $this->datatrans_sign);

        $objTemplate = new \Isotope\Template('iso_payment_datatrans');
        $objTemplate->id = $this->id;
        $objTemplate->action = ('https://' . ($this->debug ? 'pilot' : 'payment') . '.datatrans.biz/upp/jsp/upStart.jsp');
        $objTemplate->params = $arrParams;
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

        return $objTemplate->parse();
    }


    /**
     * Validate array of post parameter agains required values
     * @param array
     * @return boolean
     */
    private function validateParameters(array $arrData)
    {
        foreach ($arrData as $key => $value)
        {
            if (\Input::post($key) != $value)
            {
                \System::log('Wrong data for parameter "' . $key . '" (Order ID "' . \Input::post('refno') . ').', __METHOD__, TL_ERROR);

                return false;
            }
        }

        return true;
    }
}
