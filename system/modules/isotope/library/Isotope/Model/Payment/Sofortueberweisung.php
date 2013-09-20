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


class Sofortueberweisung extends Postsale implements IsotopePayment
{

    /**
     * sofortueberweisung.de only supports these currencies
     * @return  true
     */
    public function isAvailable()
    {
        if (!in_array(Isotope::getConfig()->currency, array('EUR', 'CHF', 'GBP'))) {
            return false;
        }

        return parent::isAvailable();
    }


    /**
     * Handle the server to server postsale request
     */
    public function processPostsale()
    {
        // check if there is a order with this ID
        if (($objOrder = Order::findByPk(\Input::post('user_variable_0'))) === null) {
            \System::log('Order not found. (Sofortüberweisung.de)', __METHOD__, TL_ERROR);
            return;
        }

        $arrHash = array (
            'transaction'                => \Input::post('transaction'),
            'user_id'                    => \Input::post('user_id'),
            'project_id'                 => \Input::post('project_id'),
            'sender_holder'              => \Input::post('sender_holder'),
            'sender_account_number'      => \Input::post('sender_account_number'),
            'sender_bank_code'           => \Input::post('sender_bank_code'),
            'sender_bank_name'           => \Input::post('sender_bank_name'),
            'sender_bank_bic'            => \Input::post('sender_bank_bic'),
            'sender_iban'                => \Input::post('sender_iban'),
            'sender_country_id'          => \Input::post('sender_country_id'),
            'recipient_holder'           => \Input::post('recipient_holder'),
            'recipient_account_number'   => \Input::post('recipient_account_number'),
            'recipient_bank_code'        => \Input::post('recipient_bank_code'),
            'recipient_bank_name'        => \Input::post('recipient_bank_name'),
            'recipient_bank_bic'         => \Input::post('recipient_bank_bic'),
            'recipient_iban'             => \Input::post('recipient_iban'),
            'recipient_country_id'       => \Input::post('recipient_country_id'),
            'international_transaction'  => \Input::post('international_transaction'),
            'amount'                     => \Input::post('amount'),
            'currency_id'                => \Input::post('currency_id'),
            'reason_1'                   => \Input::post('reason_1'),
            'reason_2'                   => \Input::post('reason_2'),
            'security_criteria'          => \Input::post('security_criteria'),
            'user_variable_0'            => \Input::post('user_variable_0'),
            'user_variable_1'            => \Input::post('user_variable_1'),
            'user_variable_2'            => \Input::post('user_variable_2'),
            'user_variable_3'            => \Input::post('user_variable_3'),
            'user_variable_4'            => \Input::post('user_variable_2'),
            'user_variable_5'            => \Input::post('user_variable_5'),
            'created'                    => \Input::post('created'),
            'notification_password'      => $this->sofortueberweisung_project_password,
        );

        // check if both hashes math
        if (\Input::post('hash') != sha1(implode('|', $arrHash))) {
            \System::log('The given hash does not match. (sofortüberweisung.de)', __METHOD__, TL_ERROR);
            return;
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('user_variable_0') . '" failed', __METHOD__, TL_ERROR);
            return;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();
    }


    /**
     * Return the payment form
     * @return string
     */
    public function checkoutForm()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null) {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $strCountry = in_array(Isotope::getCart()->getBillingAddress()->country, array('de','ch','at')) ? Isotope::getCart()->getBillingAddress()->country : 'de';
        $strUrl = 'https://www.sofortueberweisung.'.$strCountry.'/payment/start';

        $arrParam = array
        (
            'user_id'               => $this->sofortueberweisung_user_id,
            'project_id'            => $this->sofortueberweisung_project_id,
            'sender_holder'         => '',
            'sender_account_number' => '',
            'sender_bank_code'      => '',
            'sender_country_id'     => Isotope::getCart()->getBillingAddress()->country,
            'amount'                => number_format(Isotope::getCart()->getTotal(), 2, '.', ''),
            'currency_id'           => Isotope::getConfig()->currency,
            'reason_1'              => \Environment::get('host'),
            'reason_2'              => '',
            'user_variable_0'       => $objOrder->id,
            'user_variable_1'       => $this->id,
            'user_variable_2'       => $objOrder->uniqid,
            'user_variable_3'       => '',
            'user_variable_4'       => '',
            'user_variable_5'       => '',
            'project_password'      => $this->sofortueberweisung_project_password,
        );

        $arrParam['hash'] = sha1(implode('|', $arrParam));
        $arrParam['language_id'] = $GLOBALS['TL_LANGUAGE'];

        $objTemplate = new \Isotope\Template('iso_payment_sofortueberweisung');
        $objTemplate->setData($this->arrData);
        $objTemplate->action = $strUrl;
        $objTemplate->params = array_filter(array_diff_key($arrParams, array('project_password'=>'')));

        return $objTemplate->parse();
    }
}

