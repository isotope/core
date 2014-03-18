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

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Order;


class Skrill extends Postsale implements IsotopePayment
{

    /**
     * Check if payment module is available
     * @return  bool
     */
    public function isAvailable()
    {
        // Skrill only supports these currencies
        if (!in_array(Isotope::getConfig()->currency, array('EUR', 'USD', 'GBP', 'HKD', 'SGD', 'JPY', 'CAD', 'AUD', 'CHF', 'DKK', 'SEK', 'NOK', 'ILS', 'MYR', 'NZD', 'TRY', 'AED', 'MAD', 'QAR', 'SAR', 'TWD', 'THB', 'CZK', 'HUF', 'SKK', 'EEK', 'BGN', 'PLN', 'ISK', 'INR', 'LVL', 'KRW', 'ZAR', 'RON', 'HRK', 'LTL', 'JOD', 'OMR', 'RSD', 'TND'))) {
            return false;
        }

        // Skrill does not accept customers from the following countries: Afghanistan, Cuba, Myanmar, Nigeria, North Korea, Sudan, Syria, Somalia, and Yemen.
        if (in_array(Isotope::getCart()->getBillingAddress()->country, array('af', 'cu', 'mm', 'ng', 'kp', 'sd', 'sy', 'so', 'ye'))) {
            return false;
        }

        return parent::isAvailable();
    }

    /**
     * Process PayPal Instant Payment Notifications (IPN)
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        $blnValid = true;

        if (\Input::post('md5sig') != strtoupper(md5(\Input::post('marchant_id') . \Input::post('transaction_id') . strtoupper(md5($this->skrill_secret)) . \Input::post('mb_amount') . \Input::post('mb_currency') . \Input::post('status')))) {
            \System::log('MD5 Hash validation failed', __METHOD__, TL_ERROR);
            $blnValid = false;
        }

        if (\Input::post('pay_to_email') != $this->skrill_pay_to_email) {
            \System::log('Pay to email does not match (' . \Input::post('pay_to_email') . ' vs ' . $this->skrill_pay_to_email . ')', __METHOD__, TL_ERROR);
            $blnValid = false;
        }

        if (\Input::post('amount') != $objOrder->getTotal() || \Input::post('currency') !== $objOrder->currency) {
            \System::log(sprintf(
                'Order total does not match (%s %s vs %s %s)',
                \Input::post('currency'),
                \Input::post('mb_amount'),
                $objOrder->currency,
                $objOrder->getTotal()
            ), __METHOD__, TL_ERROR);

            $blnValid = false;
        }

        if ($blnValid && \Input::post('status') >= 0) {
            $objOrder->checkout();

            if (\Input::post('status') == 2) {
                $objOrder->date_paid = time();
            }

            $objOrder->updateOrderStatus($this->new_order_status);
            $objOrder->save();
        }

        // 200 OK
        $objResponse = new Response();
        $objResponse->send();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('transaction_id'));
    }

    /**
     * Return the PayPal form.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $arrData = array();
        $objRequest = new \Request();
        $objAddress = $objOrder->getBillingAddress();
        $arrSubdivisions = \Isotope\Backend::getSubdivisions();

        if ($this->skrill_parameters != '') {
            parse_str($this->skrill_parameters, $arrData);
        }

        // Merchant Details
        $arrData['pay_to_email']    = $this->skrill_pay_to_email;
        $arrData['transaction_id']  = $objOrder->id;
        $arrData['return_url']      = (\Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder));
        $arrData['cancel_url']      = (\Environment::get('base') . $objModule->generateUrlForStep('failed'));
        $arrData['status_url']      = (\Environment::get('base') . '/system/modules/isotope/postsale.php?mod=pay&id=' . $this->id);
        $arrData['language']        = $GLOBALS['TL_LANGUAGE'];
        $arrData['prepare_only']    = '1';

        // Customer Details
        $arrData['pay_from_email']  = $objAddress->email;
        $arrData['title']           = ($objAddress->gender == 'male' ? 'Mr' : ($objAddress->gender == 'female' ? 'Mrs' : null));
        $arrData['firstname']       = $objAddress->firstname;
        $arrData['lastname']        = $objAddress->lastname;
        $arrData['date_of_birth']   = ($objAddress->dateOfBirth != '' ? date('dmY', $objAddress->dateOfBirth) : null);
        $arrData['address']         = $objAddress->street_1;
        $arrData['address2']        = $objAddress->street_2;
        $arrData['phone_number']    = str_replace(array('+', ' '), array('00', ''), $objAddress->phone);
        $arrData['postal_code']     = preg_replace('/[^A-Z0-9]/i', '', $objAddress->postal);
        $arrData['city']            = $objAddress->city;
        $arrData['state']           = $arrSubdivisions[$objAddress->subdivision];
        $arrData['country']         = \Haste\Util\CountryInfo::getPropertyForCountryCode('ISO3', $objAddress->country);

        // Payment Details
        $arrData['amount']          = $this->formatAmount($objOrder->getTotal());
        $arrData['currency']        = $objOrder->currency;

        $objRequest->send('https://www.moneybookers.com/app/payment.pl', http_build_query($arrData), 'POST');
        $arrHeaders = $objRequest->headers;

        preg_match('/SESSION_ID=([^;]+)/', $arrHeaders['Set-Cookie'], $arrMatches);
        $strSession = $arrMatches[1];

        $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="1; URL=https://www.moneybookers.com/app/payment.pl?sid=' . urlencode($strSession) . '">';
    }


    protected function formatAmount($varAmount)
    {
        return preg_replace('/^(\d+)((\.00)|(\.\d)0)$/', '\1\4', (string) $varAmount);
    }
}
