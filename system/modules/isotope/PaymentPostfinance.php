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
 * @copyright  Winans Creative 2009
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
		if ($this->debug) $this->log('Post-sale request from Postfinance: '.print_r($_POST, true), 'PaymentPostfinance postProcessPayment()', TL_ACCESS);
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_order WHERE order_id=?")->limit(1)->execute($this->getRequestData('orderID'));
		
		if (!$objOrder->numRows)
		{
			$this->log('Order ID "' . $this->getRequestData('orderID') . '" not found', 'PaymentPostfinance postProcessPayment()', TL_ERROR);
			return;
		}
		elseif ($this->getRequestData('NCERROR') > 0)
		{
			$this->log('Order ID "' . $this->getRequestData('orderID') . '" has NCERROR ' . $this->getRequestData('NCERROR'), 'PaymentPostfinance postProcessPayment()', TL_ERROR);
			return;
		}
		
		// Set the current system to the language when the user placed the order.
		// This will result in correct e-mails and payment description.
		$GLOBALS['TL_LANGUAGE'] = $objOrder->language;
		$this->loadLanguageFile('default');
		
		// Load / initialize data
		$arrSet[] = array();
		if (!is_array($arrSet['payment_data'] = deserialize($objOrder->payment_data))) $arrSet['payment_data'] = array();
		
		// Store request data in order for future references
		$arrSet['payment_data']['POSTSALE'][] = $this->postfinance_method == 'GET' ? $_GET : $_POST;
		
		
		$arrData = $objOrder->row();
		$arrData['old_payment_status'] = $GLOBALS['TL_LANG']['MSC']['payment_status_labels'][$arrSet['payment_data']['status']];
		
		
		switch( $this->getRequestData('STATUS') )
		{
			case 1:			// cancelled by customer
			case 6:
			case 7:
				$arrSet['payment_data']['status'] = 'cancelled';
				$arrSet['status'] = 'cancelled';
				break;
				
			case 2:			// acquirer declines the authorization more than the maximum permissible number of times
			case 93:
				$arrSet['payment_data']['status'] = 'failed';
				break;
			
			case 51:
			case 52:
			case 59:
			case 9:			// Authorized
				$arrSet['payment_data']['status'] = 'processing';
				break;
			
			case 5:			// Accepted
				$arrSet['payment_data']['status'] = 'paid';
				break;
				
			case 0:			// Uncertain result
			case 52:
			case 92:
				$arrSet['payment_data']['status'] = 'on_hold';
				break;
				
			case 4:			// Pending
			default:
				$arrSet['payment_data']['status'] = 'pending';
				break;
		}
		
		$this->Database->prepare("UPDATE tl_iso_orders %s WHERE id=?")->set($arrSet)->execute($objOrder->id);
		
		$arrData['new_payment_status'] = $GLOBALS['TL_LANG']['MSC']['payment_status_labels'][$arrSet['payment_data']['status']];
		
		if ($this->postsale_mail)
		{
			$_SESSION['isotope']['store_id'] = $objOrder->store_id;
			$this->Import('Isotope');
			
			$this->Isotope->sendMail($this->postsale_mail, $GLOBALS['TL_ADMIN_EMAIL'], $GLOBALS['TL_LANGUAGE'], $arrData);
		}
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
		
		$arrData = array
		(
			'PSPID'			=> $this->postfinance_pspid,
			'currency'		=> $this->Store->currency,
			'SHASign'		=> sha1($objOrder->order_id . ($this->Cart->grandTotal * 100) . $this->Store->currency . $this->postfinance_pspid . $this->postfinance_secret),
		);
		
		$this->Database->prepare("UPDATE tl_iso_orders SET payment_data=? WHERE id=?")->execute(serialize($arrData), $objOrder->id);
		
		return '
<form method="post" action="' . $strAction . '">
<input type="hidden" name="PSPID" value="' . $this->postfinance_pspid . '">
<input type="hidden" name="orderID" value="' . $objOrder->order_id . '">
<input type="hidden" name="amount" value="' . ($this->Cart->grandTotal * 100) . '">
<input type="hidden" name="currency" value="' . $arrData['currency'] . '">
<input type="hidden" name="language" value="' . $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']) . '">
<input type="hidden" name="EMAIL" value="' . $arrAddress['email'] . '">
<input type="hidden" name="ownerZIP" value="' . $arrAddress['postal'] . '">
<input type="hidden" name="owneraddress" value="' . $arrAddress['street'] . '">
<input type="hidden" name="ownercty" value="' . $arrAddress['country'] . '">
<input type="hidden" name="ownertown" value="' . $arrAddress['city'] . '">
<input type="hidden" name="ownertelno" value="' . $arrAddress['phone'] . '">
<input type="hidden" name="SHASign" value="' . $arrData['SHASign'] . '">
<!-- post payment redirection: see chapter 8.2 -->
<input type="hidden" name="accepturl" value="' . $this->Environment->base . $this->addToUrl('step=order_complete') . '">
<input type="hidden" name="declineurl" value="' . $this->Environment->base . $this->addToUrl('step=order_failed') . '">
<input type="hidden" name="exceptionurl" value="' . $this->Environment->base . $this->addToUrl('step=order_failed') . '">
<input type="hidden" name="cancelurl" value="' . $this->Environment->base . $this->addToUrl('step=order_failed') . '">
<input type="hidden" name="paramplus" value="mod=pay&id=' . $this->id . '">
<input type="submit" value="Bezahlen">
</form>
';
	}
	
	
	private function getRequestData($strKey)
	{
		if ($this->postfinance_method == 'GET')
			return $this->Input->get($strKey);
			
		return $this->Input->post($strKey);
	}
}

