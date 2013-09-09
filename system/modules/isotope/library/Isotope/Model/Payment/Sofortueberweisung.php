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
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;


class Sofortueberweisung extends Payment implements IsotopePayment
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
     * Process payment.
     *
     * @access public
     * @return void
     */
    public function processPayment()
    {
        // sofortueberweisung.de does not provide any possibility to verify the transaction through the return URL.
        // The user must enable the post-sale request.
        return true;
    }


    /**
     * Handle the server to server postsale request
     *
     * @param array $arrRow
     * @return void
     */
    public function processPostSale($arrRow)
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
        if (\Input::post('hash') == sha1(implode('|', $arrHash))) {

            $objOrder->date_paid = time();
            $objOrder->save();
            return;
        }

        // error, hashes does not match
        \System::log('The given hash does not match. (sofortüberweisung.de)', __METHOD__, TL_ERROR);
    }


    /**
     * Return the payment form.
     *
     * @access public
     * @return string
     */
    public function checkoutForm()
    {
        if (($objOrder = Order::findOneBy('cart_id', Isotope::getCart()->id)) === null) {
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
            'user_variable_2'       => '',
            'user_variable_3'       => '',
            'user_variable_4'       => '',
            'user_variable_5'       => '',
            'project_password'      => $this->sofortueberweisung_project_password,
        );

        $arrParam['hash'] = sha1(implode('|', $arrParam));
        $arrParam['language_id'] = $GLOBALS['TL_LANGUAGE'];


        $strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<form id="payment_form" action="' . $strUrl . '" method="post">';

        foreach( $arrParam as $k => $v )
        {
            if ($v == '' || $k == 'project_password')
                continue;

            $strBuffer .= "\n" . '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
        }

        $strBuffer .= '
<noscript>
<input type="submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]) . '">
</noscript>
</form>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent( \'domready\' , function() {
  $(\'payment_form\').submit();
});
//--><!]]>
</script>';

        return $strBuffer;
    }
}

