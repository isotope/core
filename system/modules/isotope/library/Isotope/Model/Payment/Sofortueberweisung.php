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
use Isotope\Isotope;
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
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        $arrHash = array(
            'transaction'               => \Input::post('transaction'),
            'user_id'                   => \Input::post('user_id'),
            'project_id'                => \Input::post('project_id'),
            'sender_holder'             => \Input::post('sender_holder'),
            'sender_account_number'     => \Input::post('sender_account_number'),
            'sender_bank_code'          => \Input::post('sender_bank_code'),
            'sender_bank_name'          => \Input::post('sender_bank_name'),
            'sender_bank_bic'           => \Input::post('sender_bank_bic'),
            'sender_iban'               => \Input::post('sender_iban'),
            'sender_country_id'         => \Input::post('sender_country_id'),
            'recipient_holder'          => \Input::post('recipient_holder'),
            'recipient_account_number'  => \Input::post('recipient_account_number'),
            'recipient_bank_code'       => \Input::post('recipient_bank_code'),
            'recipient_bank_name'       => \Input::post('recipient_bank_name'),
            'recipient_bank_bic'        => \Input::post('recipient_bank_bic'),
            'recipient_iban'            => \Input::post('recipient_iban'),
            'recipient_country_id'      => \Input::post('recipient_country_id'),
            'international_transaction' => \Input::post('international_transaction'),
            'amount'                    => \Input::post('amount'),
            'currency_id'               => \Input::post('currency_id'),
            'reason_1'                  => \Input::post('reason_1'),
            'reason_2'                  => \Input::post('reason_2'),
            'security_criteria'         => \Input::post('security_criteria'),
            'user_variable_0'           => \Input::post('user_variable_0'),
            'user_variable_1'           => \Input::post('user_variable_1'),
            'user_variable_2'           => \Input::post('user_variable_2'),
            'user_variable_3'           => \Input::post('user_variable_3'),
            'user_variable_4'           => \Input::post('user_variable_4'),
            'user_variable_5'           => \Input::post('user_variable_5'),
            'created'                   => \Input::post('created'),
            'notification_password'     => $this->sofortueberweisung_project_password,
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
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('user_variable_0'));
    }

    /**
     * Return the payment form
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $strCountry = in_array($objOrder->getBillingAddress()->country, array('de', 'ch', 'at')) ? $objOrder->getBillingAddress()->country : 'de';
        $strUrl     = 'https://www.sofortueberweisung.' . $strCountry . '/payment/start';

        $arrParams = array
        (
            'user_id'               => $this->sofortueberweisung_user_id,
            'project_id'            => $this->sofortueberweisung_project_id,
            'sender_holder'         => '',
            'sender_account_number' => '',
            'sender_bank_code'      => '',
            'sender_country_id'     => strtoupper($objOrder->getBillingAddress()->country),
            'amount'                => number_format($objOrder->getTotal(), 2, '.', ''),
            'currency_id'           => $objOrder->currency,
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

        $arrParams['hash']        = sha1(implode('|', $arrParams));
        $arrParams['language_id'] = $GLOBALS['TL_LANGUAGE'];

        $objTemplate = new \Isotope\Template('iso_payment_sofortueberweisung');
        $objTemplate->setData($this->arrData);
        $objTemplate->action   = $strUrl;
        $objTemplate->params   = array_filter(array_diff_key($arrParams, array('project_password' => '')));
        $objTemplate->headline = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }
}

