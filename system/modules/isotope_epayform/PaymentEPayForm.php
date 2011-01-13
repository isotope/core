<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
/**
 * Handle Paypal payments
 * 
 * @extends Payment
 */
class PaymentEPayForm extends PaymentEPay
{
	
	/**
	 * processPayment function.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->executeUncached($this->Isotope->Cart->id);
		$intTotal = $this->Isotope->Cart->grandTotal * 100;
		
		// Check basic order data
		if ($this->Input->get('orderid') == $objOrder->id && $this->Input->get('cur') == $this->arrCurrencies[$this->Isotope->Config->currency] && $this->Input->get('amount') == (string)$intTotal)
		{
			// Validate MD5 secret key
			if (md5($intTotal . $objOrder->id . $this->Input->get('tid') . $this->epay_secretkey) == $this->Input->get('eKey'))
			{
				return true;
			}
		}
		
		global $objPage;
		$this->log('Invalid payment data received.', 'PaymentEPayForm processPayment()', TL_ERROR);
		$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/process'));
	}
	
	
	/**
	 * Return the payment form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		global $objPage;
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->executeUncached($this->Isotope->Cart->id);
		$intTotal = round($this->Isotope->Cart->grandTotal, 2) * 100;
		
		$strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_cc'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_cc'][1] . '</p>' . 
($this->Input->get('error') == '' ? '' : '<p class="error message">'.$GLOBALS['TL_LANG']['MSG']['epay'][$this->Input->get('error')].'</p>') . '
<form id="payment_form" action="https://ssl.ditonlinebetalingssystem.dk/auth/default.aspx" method="post">

<table cellspacing="0" cellpadding="0" summary="ePay Payment Form">
	<tr class="cardno">
		<td><label for="ctrl_cardno">' . $GLOBALS['TL_LANG']['ISO']['cc_num'] . '<label> <span class="mandatory">*</span></td>
		<td><input type="text" class="text" id="ctrl_cardno" name="cardno" maxlength="19" autocomplete="off" /></td>
	</tr>
	<tr class="expdate">
		<td><label for="ctrl_expmonth">' . $GLOBALS['TL_LANG']['ISO']['cc_exp_date'] . '</label> <span class="mandatory">*</span></td>
		<td>
			<select id="ctrl_expmonth" name="expmonth" class="select">';
		
		foreach( range(1, 12) as $month )
		{
			$month = str_pad($month, 2, '0', STR_PAD_LEFT);
			$strBuffer .= '<option value="' . $month . '">' . $month . '</option>';
		}
		
		$strBuffer .= '</select>&nbsp;<select id="ctrl_expyear" name="expyear" class="select">';
		
		for( $now=date('Y'), $year=$now; $year<=$now+12; $year++ )
		{
			$strBuffer .= '<option value="' . substr($year, -2) . '">' . $year . '</option>';
		}
		
		$strBuffer .= '</select>
		</td>
	</tr>
	<tr class="cvc">
		<td><label for="ctrl_cvc">' . $GLOBALS['TL_LANG']['ISO']['cc_ccv'] . '</label></td>
		<td><input type="text" class="text" name="cvc" id="ctrl_cvc" maxlength="4" autocomplete="off" /></td>
	</tr>
</table>


<input type="hidden" name="merchantnumber" value="' . $this->epay_merchantnumber . '">
<input type="hidden" name="orderid" value="' . $objOrder->id . '">
<input type="hidden" name="description" value="' . $this->Isotope->generateAddressString($this->Isotope->Cart->billingAddress, $this->Isotope->Config->billing_fields) . '">
<input type="hidden" name="currency" value="' . $this->arrCurrencies[$this->Isotope->Config->currency] . '">
<input type="hidden" name="amount" value="' . $intTotal . '">

<input type="hidden" name="accepturl" value="' . ($GLOBALS['EPAY_RELAY'] ? 'https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/' : '') . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/complete') . ($GLOBALS['EPAY_RELAY'] ? '?HTTP_COOKIE='.$_SERVER['HTTP_COOKIE'] : '') . '">
<input type="hidden" name="declineurl" value="' . ($GLOBALS['EPAY_RELAY'] ? 'https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/' : '') . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/process') . ($GLOBALS['EPAY_RELAY'] ? '?HTTP_COOKIE='.$_SERVER['HTTP_COOKIE'] : '') . '">

<input type="hidden" name="language" value="2">
<input type="hidden" name="instantcapture" value="' . ($this->trans_type == 'auth' ? '0' : '1') . '">
<input type="hidden" name="md5key" value="' . md5($this->arrCurrencies[$this->Isotope->Config->currency] . $intTotal . $objOrder->id . $this->epay_secretkey) . '">
<input type="hidden" name="cardtype" value="0">
<input type="hidden" name="use3D" value="1">

<div class="submit_container">
<input type="submit" class="submit button" value="' . $GLOBALS['TL_LANG']['MSC']['pay_with_cc'][2] . '" />
<a class="button" href="' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/failed') . '">Cancel</a>
</div>

</form>';

		return $strBuffer;
	}
}

