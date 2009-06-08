<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
/**
 * Handle Postfinance (swiss post) payments
 * 
 * @extends Payment
 */
class PaymentPostfinance extends Payment
{

	/**
	 * processPayment function.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		return true;
		// Reload page every 5 seconds and check if payment was successful
//		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="5,http://...">';
	}
	
	
	/**
	 * Return the PayPal form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$this->import('IsotopeStore', 'Store');
		$this->import('IsotopeCart', 'Cart');
		
		$strAction = 'https://e-payment.postfinance.ch/ncol/prod/orderstandard.asp';
		
		if ($this->debug)
		{
			$strAction = 'https://e-payment.postfinance.ch/ncol/test/orderstandard.asp';
		}
		
		return '
<form method="post" action="' . $strAction . '"
id=form1 name=form1>
<!-- general parameters: see chapter 5.2 -->
<input type="hidden" name="PSPID" value="' . $this->postfinance_pspid . '">
<input type="hidden" name="orderID" value="">
<input type="hidden" name="amount" value="' . ($this->Cart->grandTotal * 100) . '">
<input type="hidden" name="currency" value="' . $this->Store->currency . '">
<input type="hidden" name="language" value="' . $GLOBALS['TL_LANGUAGE'] . '">
<!-- optional customer details, highly recommended for fraud prevention: see chapter 5.2 -->
<input type="hidden" name="CN" value="">
<input type="hidden" name="EMAIL" value="">
<input type="hidden" name="ownerZIP" value="">
<input type="hidden" name="owneraddress" value="">
<input type="hidden" name="ownercty" value="">
<input type="hidden" name="ownertown" value="">
<input type="hidden" name="ownertelno" value="">
<input type="hidden" name="COM" value="">
<!-- check before the payment: see chapter 6.2 -->
<input type="hidden" name="SHASign" value="">
<!-- layout information: see chapter 7.1 -->
<input type="hidden" name="TITLE" value="">
<input type="hidden" name="BGCOLOR" value="">
<input type="hidden" name="TXTCOLOR" value="">
<input type="hidden" name="TBLBGCOLOR" value="">
<input type="hidden" name="TBLTXTCOLOR" value="">
<input type="hidden" name="BUTTONBGCOLOR" value="">
<input type="hidden" name="BUTTONTXTCOLOR" value="">
<input type="hidden" name="LOGO" value="">
<input type="hidden" name="FONTTYPE" value="">
<!-- dynamic template page: see chapter 7.2 -->
<input type="hidden" name="TP" value="">
<!-- payment methods/page specifics: see chapter 9.1 -->
<input type="hidden" name="PM" value="">
<input type="hidden" name="BRAND" value="">
<input type="hidden" name="WIN3DS" value="">
<input type="hidden" name="PM list type" value="">
<input type="hidden" name="PMListType" value="">
<!-- link to your website: see chapter 8.1 -->
<input type="hidden" name="homeurl" value="">
<input type="hidden" name="catalogurl" value="">
<!-- post payment parameters: see chapter 8.2 -->
<input type="hidden" name="COMPLUS" value="">
<input type="hidden" name="PARAMPLUS" value="">
<!-- post payment parameters: see chapter 8.3 -->
<input type="hidden" name="PARAMVAR" value="">
<!-- post payment redirection: see chapter 8.2 -->
<input type="hidden" name="accepturl" value="">
<input type="hidden" name="declineurl" value="">
<input type="hidden" name="exceptionurl" value="">
<input type="hidden" name="cancelurl" value="">
<!-- optional operation field: see chapter 9.2 -->
<input type="hidden" name="operation" value="">
<!-- optional extra login field: see chapter 9.3 -->
<input type="hidden" name="USERID" value="">
<!-- Alias details: see Alias Management documentation -->
<input type="hidden" name="Alias" value="">
<input type="hidden" name="AliasUsage" value="">
<input type="hidden" name="AliasOperation" value="">
<input type="submit" value="Bezahlen" id=submit2 name=submit2>
</form>
';
	}
}