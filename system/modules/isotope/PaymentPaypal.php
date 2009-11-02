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
 
 
 
$_POST = Array
(
	'test_ipn' => 1,
	'payment_type' => 'instant',
	'payment_date' => '04:57:38 Nov. 02, 2009 PST',
	'payment_status' => 'Completed',
	'address_status' => 'confirmed',
	'payer_status' => 'unverified',
	'first_name' => 'John',
	'last_name' => 'Smith',
	'payer_email' => 'buyer@paypalsandbox.com',
	'payer_id' => 'TESTBUYERID01',
	'address_name' => 'John Smith',
	'address_country' => 'United States',
	'address_country_code' => 'US',
	'address_zip' => '95131',
	'address_state' => 'CA',
	'address_city' => 'San Jose',
	'address_street' => '123, any street',
	'receiver_email' => 'info@iserv.ch',
	'receiver_id' => 'TESTSELLERID1',
	'residence_country' => 'US',
	'item_name1' => 'something',
	'item_number1' => 'AK-1234',
	'quantity1' => '1',
	'tax' => '2.02',
	'mc_currency' => 'USD',
	'mc_fee' => '0.44',
	'mc_gross_1' => '9.34',
	'mc_handling' => '2.06',
	'mc_handling1' => '1.67',
	'mc_shipping' => '3.02',
	'mc_shipping1' => '1.02',
	'txn_type' => 'cart',
	'txn_id' => '381121257',
	'notify_version' => '2.4',
	'custom' => 'xyz123',
	'invoice' => '4',
	'charset' => 'windows-1252',
	'verify_sign' => 'AjwFMJyD73RJI4g212S5GEDE3DWQAP.psGy.fNTeMFVaNTjwzeltLLR4'
);

 
/**
 * Handle Paypal payments
 * 
 * @extends Payment
 */
class PaymentPaypal extends Payment
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
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale() 
	{
		$arrData = array();
		foreach( $_POST as $k => $v )
		{
			$arrData[] = $k . '=' . $v;
		}

		$objRequest = new Request();
		$objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate'), implode('&', $arrData), 'post');
		
		if ($objRequest->response == 'VERIFIED' && $this->Input->post('receiver_email') == $this->paypal_account)
		{
			$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE order_id=?")->limit(1)->execute($this->Input->post('invoice'));
		
			if (!$objOrder->numRows)
			{
				$this->log('Order ID "' . $this->Input->post('invoice') . '" not found', 'PaymentPaypal processPostSale()', TL_ERROR);
				return;
			}

			// Set the current system to the language when the user placed the order.
			// This will result in correct e-mails and payment description.
			$GLOBALS['TL_LANGUAGE'] = $objOrder->language;
			$this->loadLanguageFile('default');
			
			// Load / initialize data
			$arrSet = array();
			if (!is_array($arrSet['payment_data'] = deserialize($objOrder->payment_data))) $arrSet['payment_data'] = array();
			
			// Store request data in order for future references
			$arrSet['payment_data']['POSTSALE'][] = $_POST;
			
			
			$arrData = $objOrder->row();
			$arrData['old_payment_status'] = $GLOBALS['TL_LANG']['MSC']['payment_status_labels'][$arrSet['payment_data']['status']];
			
			$arrSet['payment_data']['status'] = $this->Input->post('payment_status');
			
			// array('pending','processing','shipped','complete','on_hold', 'cancelled'),
			switch( $this->Input->post('payment_status') )
			{
				case 'Completed':
					break;
					
				case 'Canceled_Reversal':
				case 'Denied':
				case 'Expired':
				case 'Failed':
				case 'Voided':
					$arrSet['status'] = 'cancelled';
					break;
					
				case 'In-Progress':
				case 'Partially_Refunded':
				case 'Pending':
				case 'Processed':
				case 'Refunded':
				case 'Reversed':
					break;
			}
			
			$this->Database->prepare("UPDATE tl_iso_orders %s WHERE id=?")->set($arrSet)->execute($objOrder->id);
			
			$arrData['new_payment_status'] = $GLOBALS['TL_LANG']['MSC']['payment_status_labels'][$arrSet['payment_data']['status']];
			
			if ($this->postsale_mail)
			{
				$this->Import('Isotope');
				$this->Isotope->overrideStore($objOrder->store_id);
				$this->Isotope->sendMail($this->postsale_mail, $GLOBALS['TL_ADMIN_EMAIL'], $GLOBALS['TL_LANGUAGE'], $arrData);
			}
			
			$this->log('PayPal IPN: data accepted ' . print_r($_POST, true), 'PaymentPaypal processPostSale()', TL_GENERAL);
		}
		else
		{
			$this->log('PayPal IPN: data rejected (' . $objRequest->response . ') ' . print_r($_POST, true), 'PaymentPaypal processPostSale()', TL_GENERAL);
		}
		
		header('HTTP/1.1 200 OK');
		exit;
	}
	
	
	/**
	 * Return the PayPal form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$this->import('Isotope');
		$this->import('IsotopeCart', 'Cart');
		
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Cart->id);
		
		return '
<form action="https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="' . $this->paypal_account . '">
<input type="hidden" name="lc" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '">
<input type="hidden" name="item_name" value="' . $this->paypal_business . '"/>
<input type="hidden" name="amount" value="' . $this->Cart->subTotal . '"/>
<input type="hidden" name="shipping" value="' . $this->Cart->shippingTotal . '">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="' . $this->Isotope->Store->currency . '">
<input type="hidden" name="button_subtype" value="services">
<input type="hidden" name="return" value="' . $this->Environment->url . '/' . $this->addToUrl('step=order_complete') . '">
<input type="hidden" name="cancel_return" value="' . $this->Environment->url . '/' . $this->addToUrl('step=order_failed') . '">
<input type="hidden" name="rm" value="0">
<input type="hidden" name="invoice" value="' . $objOrder->order_id . '">
<input type="hidden" name="notify_url" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
<input type="image" src="https://www.paypal.com/de_DE/CH/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>';
	}
}

