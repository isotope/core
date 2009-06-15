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
		$this->log('Post-sale request from Postfinance: '.print_r($_GET, true).print_r($_POST, true), 'PaymentPostfinance postProcessPayment()', TL_ACCESS);
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
		
		$strAction = 'https://e-payment.postfinance.ch/ncol/prod/orderstandard.asp';
		
		if ($this->debug)
		{
			$strAction = 'https://e-payment.postfinance.ch/ncol/test/orderstandard.asp';
		}
		
		return '
<form method="post" action="' . $strAction . '">
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
';
	}
}