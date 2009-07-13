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
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
/**
 * Handle Postfinance (swiss post) payments
 * 
 * @extends Payment
 */
class PaymentAuthorizeDotNet extends Payment
{

	/**
	 * Process payment on confirmation page.
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
	 * Process post-sale requestion from the Postfinance payment server.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPostSale()
	{
		$this->log('Post-sale request from Authorize.net: '.print_r($_GET, true).print_r($_POST, true), 'PaymentAuthorizeDotNet postProcessPayment()', TL_ACCESS);
		
		//for Authorize.net - this would be where to handle logging response information from the server.
		
	}
	
	
	/**
	 * Return the payment form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$this->import('Isotope');
		$this->import('IsotopeStore', 'Store');
		$this->import('IsotopeCart', 'Cart');
		
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Cart->id);
		$arrAddress = $this->Isotope->getAddress('billing');
		
		$strTestValue = "false";
		$strCurlUrl = 'https://secure.authorize.net/gateway/transact.dll';
		
		if ($this->debug)
		{
			$strCurlUrl = 'https://test.authorize.net/gateway/transact.dll';
			$strTestValue = "true";
		}
		
		$arrData = array
		(
			'currency'		=> $this->Store->currency,
			//'SHASign'		=> sha1($objOrder->order_id . ($this->Cart->grandTotal * 100) . $this->Store->currency . $this->postfinance_pspid . $this->postfinance_secret),
		);
		
		$this->Database->prepare("UPDATE tl_iso_orders SET payment_data=? WHERE id=?")->execute(serialize($arrData), $objOrder->id);
		
		return '
<form method="post" action="' . $this->Environment->request . '">
<input type="hidden" name="x_login" value="' . $this->authorize_login . '">
<input type="hidden" name="x_version" value="' . $GLOBALS['TL_LANG']['MSC']['gatewayVersion'] . '">
<input type="hidden" name="x_test_request" value="' . $strTestValue . '">
<input type="hidden" name="x_delim_char" value="' . $this->authorize_delimiter . '">
<input type="hidden" name="x_delim_data" value="' . $this->authorize_delimit_data . '">
<input type="hidden" name="x_url" value="' . $strCurlUrl . '">
<input type="hidden" name="x_type" value="' . $this->authorize_trans_type . '">
<input type="hidden" name="x_method" value="' . $this->authorize_method . '">
<input type="hidden" name="x_tran_key" value="' . $this->authorize_trans_key . '">
<input type="hidden" name="x_relay_response" value="' . $this->authorize_relay_response . '">
<input type="hidden" name="x_card_num" value="' . $this->Input->post('cc_num') . '">
<input type="hidden" name="x_card_type" value="' . $this->Input->post('cc_type') . '">
<input type="hidden" name="x_exp_date" value="' . $this->Input->post('cc_exp') . '">
<input type="hidden" name="x_card_code" value="' . $this->Input->post('cc_cvv') . '">
<input type="hidden" name="x_description" value="New Order ID' . $objOrder->order_id . ($this->debug ? ' ' . $GLOBALS['TL_LANG']['MSC']['testTransaction'] : '') . '">
<input type="hidden" name="x_amount" value="' . $this->Cart->grandTotal . '">
<input type="hidden" name="x_first_name" value="' . $arrAddress['firstname'] . '">
<input type="hidden" name="x_last_name" value="' . $arrAddress['lastname'] . '">
<input type="hidden" name="x_address" value="' . $arrAddress['address'] . '">
<input type="hidden" name="x_city" value="' . $arrAddress['city'] . '">
<input type="hidden" name="x_state" value="' . $arrAddress['state'] . '">
<input type="hidden" name="x_zip" value="' . $arrAddress['postal'] . '">
<input type="hidden" name="x_company" value="' . $arrAddress['company'] . '">
<input type="hidden" name="x_email_customer"' . $this->authorize_email_customer . '">
<input type="hidden" name="x_email"' . $arrAddress['email'] . '">'
. $this->checkoutForm($this->id) . 
'</form>';
/*

<input type="hidden" name="PSPID" value="' . $this->postfinance_pspid . '">
<input type="hidden" name="orderID" value="' . $objOrder->order_id . '">
<input type="hidden" name="amount" value="' . ($this->Cart->grandTotal * 100) . '">
<input type="hidden" name="currency" value="' . $this->Store->currency . '">
<input type="hidden" name="language" value="' . $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']) . '">
<!-- optional customer details, highly recommended for fraud prevention: see chapter 5.2 -->
<input type="hidden" name="EMAIL" value="' . $arrAddress['email'] . '">
<input type="hidden" name="ownerZIP" value="' . $arrAddress['postal'] . '">
<input type="hidden" name="owneraddress" value="' . $arrAddress['street'] . '">
<input type="hidden" name="ownercty" value="' . $arrAddress['country'] . '">
<input type="hidden" name="ownertown" value="' . $arrAddress['city'] . '">
<input type="hidden" name="ownertelno" value="' . $arrAddress['phone'] . '">
<input type="hidden" name="SHASign" value="' . sha1($objOrder->order_id . ($this->Cart->grandTotal * 100) . $this->Store->currency . $this->postfinance_pspid . $this->postfinance_secret) . '">
<!-- post payment redirection: see chapter 8.2 -->
<input type="hidden" name="accepturl" value="' . $this->Environment->base . $this->addToUrl('step=order_complete') . '">
<input type="hidden" name="declineurl" value="">
<input type="hidden" name="exceptionurl" value="">
<input type="hidden" name="cancelurl" value="">
<input type="hidden" name="paramplus" value="do=pay&id=' . $this->id . '">
<input type="submit" value="Bezahlen">
</form>
';*/
	}
}

?>