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
	 * Return the payment form.
	 *
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);

		$strCountry = in_array($this->Isotope->Cart->billingAddress['country'], array('de','ch','at')) ? $this->Isotope->Cart->billingAddress['country'] : 'de';
		$strUrl = 'https://www.sofortueberweisung.'.$strCountry.'/payment/start';


		$arrParam = array
		(
			'user_id'				=> $this->sofortueberweisung_user_id,
			'project_id'			=> $this->sofortueberweisung_project_id,
			'sender_holder'			=> '',
			'sender_account_number'	=> '',
			'sender_bank_code'		=> '',
			'sender_country_id'		=> $this->Isotope->Cart->billingAddress['country'],
			'amount'				=> number_format($this->Isotope->Cart->grandTotal, 2, '.', ''),
			'currency_id'			=> $this->Isotope->Config->currency,
			'reason_1'				=> $this->Environment->host,
			'reason_2'				=> '',
			'user_variable_0'		=> $objOrder->id,
			'user_variable_1'		=> '',
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




		$strBuffer = "
<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function() {
	$('payment_form').submit();
});
//--><!]]>
</script>";
	}
}

