<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */

class PaymentSofortueberweisung extends IsotopePayment
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
		$this->import('Input');

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
			'transaction' => $this->Input->post('transaction'),
			'user_id' => $this->Input->post('user_id'),
			'project_id' => $this->Input->post('project_id'),
			'sender_holder' => $this->Input->post('sender_holder'),
			'sender_account_number' => $this->Input->post('sender_account_number'),
			'sender_bank_code' => $this->Input->post('sender_bank_code'),
			'sender_bank_name' => $this->Input->post('sender_bank_name'),
			'sender_bank_bic' => $this->Input->post('sender_bank_bic'),
			'sender_iban' => $this->Input->post('sender_iban'),
			'sender_country_id' => $this->Input->post('sender_country_id'),
			'recipient_holder' => $this->Input->post('recipient_holder'),
			'recipient_account_number' => $this->Input->post('recipient_account_number'),
			'recipient_bank_code' => $this->Input->post('recipient_bank_code'),
			'recipient_bank_name' => $this->Input->post('recipient_bank_name'),
			'recipient_bank_bic' => $this->Input->post('recipient_bank_bic'),
			'recipient_iban' => $this->Input->post('recipient_iban'),
			'recipient_country_id' => $this->Input->post('recipient_country_id'),
			'international_transaction' => $this->Input->post('international_transaction'),
			'amount' => $this->Input->post('amount'),
			'currency_id' => $this->Input->post('currency_id'),
			'reason_1' => $this->Input->post('reason_1'),
			'reason_2' => $this->Input->post('reason_2'),
			'security_criteria' => $this->Input->post('security_criteria'),
			'user_variable_0' => $this->Input->post('user_variable_0'),
			'user_variable_1' => $this->Input->post('user_variable_1'),
			'user_variable_2' => $this->Input->post('user_variable_2'),
			'user_variable_3' => $this->Input->post('user_variable_3'),
			'user_variable_4' => $this->Input->post('user_variable_2'),
			'user_variable_5' => $this->Input->post('user_variable_5'),
			'created' => $this->Input->post('created'),
			'notification_password' => ';,J~!}!GZJ){20)~!Cup',
		);


		$strHash = sha1(implode('|', $arrHash));

		// check if both hashes math
		if ($this->Input->post('hash') == $strHash)
		{
			$arrSet = array
			(
				'date_paid' => time()
			);

			// update the order
			$this->Database->prepare('UPDATE tl_iso_orders %s WHERE id=?')
						   ->set($arrSet)
						   ->execute($this->Input->post('user_variable_0'));

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

