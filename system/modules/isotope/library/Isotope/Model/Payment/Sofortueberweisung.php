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


class Sofortueberweisung extends Payment implements IsotopePayment
{

	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'available':
				// sofortueberweisung.de only supports these currencies
				if (!in_array($this->Isotope->Config->currency, array('EUR', 'CHF', 'GBP')))
					return false;

				// Do NOT add a break. Continue to the parent "available" check.

			default:
				return parent::__get($strKey);
		}
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
		$this->import('Database');

		// check if there is a order with this ID
		$objOrderCheck = $this->Database->prepare('SELECT * FROM tl_iso_orders WHERE id=?')
										->execute($this->Input->post('user_variable_0'));

		if ($objOrderCheck->numRows != 1)
		{
			$this->log('Order not found. (Sofortüberweisung.de)', __METHOD__, TL_ERROR);
			return;
		}


		$arrHash = array
		(
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
			'notification_password'      => ';,J~!}!GZJ){20)~!Cup',
		);


		$strHash = sha1(implode('|', $arrHash));

		// check if both hashes math
		if (\Input::post('hash') == $strHash)
		{
			$arrSet = array
			(
				'date_paid' => time()
			);

			// update the order
			$this->Database->prepare('UPDATE tl_iso_orders %s WHERE id=?')
						   ->set($arrSet)
						   ->execute(\Input::post('user_variable_0'));

			return;
		}

		// error, hashes does not match
		$this->log('The given hash does not match. (sofortüberweisung.de)', __METHOD__, TL_ERROR);
		return;
	}


	/**
	 * Return the payment form.
	 *
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);

		$strCountry = in_array($this->Isotope->Cart->billing_address['country'], array('de','ch','at')) ? $this->Isotope->Cart->billing_address['country'] : 'de';
		$strUrl = 'https://www.sofortueberweisung.'.$strCountry.'/payment/start';

		$arrParam = array
		(
			'user_id'				=> $this->sofortueberweisung_user_id,
			'project_id'			=> $this->sofortueberweisung_project_id,
			'sender_holder'			=> '',
			'sender_account_number'	=> '',
			'sender_bank_code'		=> '',
			'sender_country_id'		=> $this->Isotope->Cart->billing_address['country'],
			'amount'				=> number_format($this->Isotope->Cart->grandTotal, 2, '.', ''),
			'currency_id'			=> $this->Isotope->Config->currency,
			'reason_1'				=> $this->Environment->host,
			'reason_2'				=> '',
			'user_variable_0'		=> $objOrder->id,
			'user_variable_1'		=> $this->id,
			'user_variable_2'		=> '',
			'user_variable_3'		=> '',
			'user_variable_4'		=> '',
			'user_variable_5'		=> '',
			'project_password'		=> $this->sofortueberweisung_project_password,
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

