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
 * Handle Paypal payments
 * 
 * @extends Payment
 */
class PaymentPaypalPro extends Payment
{

	/**
	 * processPayment function.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		$this->import('IsotopeCart', 'Cart');
		$this->import('Isotope');
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Cart->id);
		
		$arrPaymentData = deserialize($objOrder->payment_data);
		
		
		$arrData = array
		(
			'METHOD'				=> 'DoDirectPayment',
			'VERSION'				=> '3.0',
			'PWD'					=> $this->paypalpro_apiPassword,
			'USER'					=> $this->paypalpro_apiUserName,
			'SIGNATURE'				=> $this->paypalpro_apiSignature,
			'PAYMENTACTION'			=> $this->paypalpro_transType,
			'IPADDRESS'				=> $this->Environment->ip,
			'AMT'					=> $this->Cart->grandTotal,
			'CREDITCARDTYPE'		=> $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_type'],
			'ACCT'					=> $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'],
			'EXPDATE'				=> $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'],
			'FIRSTNAME'				=> $this->Cart->billingAddress['firstname'],
			'LASTNAME'				=> $this->Cart->billingAddress['lastname'],
			'STREET'				=> $this->Cart->billingAddress['street_1'],
			'STREET2'				=> $this->Cart->billingAddress['street_2']."\n".$this->Cart->billingAddress['street_3'],
			'CITY'					=> $this->Cart->billingAddress['city'],
			'STATE'					=> $this->Cart->billingAddress['state'],
			'COUNTRYCODE'			=> $this->Cart->billingAddress['country'],
			'ZIP'					=> $this->Cart->billingAddress['postal'],
			//'NOTIFYURL'				=> '',
			'CURRENCYCODE'			=> $this->Isotope->Store->currency,
			'ITEMAMT'				=> $this->Cart->subTotal,
			'SHIPPINGAMT'			=> $this->Cart->shippingTotal,
			'HANDLINGAMT'			=> 0,	//TODO: support handling charges
			'TAXAMT'				=> '',
			'DESC'					=> "Order Number " . $objOrder->order_id,
			'CUSTOM'				=> '',
			'INVNUM'				=> $objOrder->id,
			'EMAIL'					=> '',
			'PHONENUM'				=> '',
			'SHIPTONAME'			=> '',
			'SHIPTOSTREET'			=> '',
			'SHIPTOSTREET2'			=> '',
			'SHIPTOCITY'			=> '',
			'SHIPTOSTATE'			=> '',
			'SHIPTOZIP'				=> '',
			'SHIPTOCOUNTRYCODE'		=> '',
			'SHIPTOPHONENUM'		=> ''
			
		);	
		
		if($this->requireCCV)
		{
			$arrData['CVV2'] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_ccv'];
		}
		
		/*	'payment_type' => array('name' => 'PAYMENTACTION', 'required' => 'yes'),
			'ip_address' => array('name' => 'IPADDRESS', 'required' => 'yes'),
			'amount_total' => array('name' => 'AMT', 'required' => 'yes'), 
			'credit_card_type' => array('name' => 'CREDITCARDTYPE', 'required' => 'yes'), 
			'credit_card_number' => array('name' => 'ACCT', 'required' => 'yes'), 
			'expire_date' => array('name' => 'EXPDATE', 'required' => 'yes'), 
			'first_name' => array('name' => 'FIRSTNAME', 'required' => 'yes'), 
			'last_name' => array('name' => 'LASTNAME', 'required' => 'yes'), 
			'address1' => array('name' => 'STREET', 'required' => 'no'), 
			'address2' => array('name' => 'STREET2', 'required' => 'no'), 
			'city' => array('name' => 'CITY', 'required' => 'no'), 
			'state' => array('name' => 'STATE', 'required' => 'no'), 
			'country_code' => array('name' => 'COUNTRYCODE', 'required' => 'no'), 
			'postal_code' => array('name' => 'ZIP', 'required' => 'no'), 
			'notify_url' => array('name' => 'NOTIFYURL', 'required' => 'no'), 
			'currency_code' => array('name' => 'CURRENCYCODE', 'required' => 'no'), 
			'amount_items' => array('name' => 'ITEMAMT', 'required' => 'no'), 
			'amount_shipping' => array('name' => 'SHIPPINGAMT', 'required' => 'no'), 
			'amount_handling' => array('name' => 'HANDLINGAMT', 'required' => 'no'), 
			'amount_tax' => array('name' => 'TAXAMT', 'required' => 'no'), 
			'description' => array('name' => 'DESC', 'required' => 'no'), 
			'custom' => array('name' => 'CUSTOM', 'required' => 'no'), 
			'invoice' => array('name' => 'INVNUM', 'required' => 'no'), 
			'cvv2_code' => array('name' => 'CVV2', 'required' => 'yes'), 
			'email' => array('name' => 'EMAIL', 'required' => 'no'), 
			'phone_number' => array('name' => 'PHONENUM', 'required' => 'no'), 
			'shipping_name' => array('name' => 'SHIPTONAME', 'required' => 'no'), 
			'shipping_address1' => array('name' => 'SHIPTOSTREET', 'required' => 'no'), 
			'shipping_address2' => array('name' => 'SHIPTOSTREET2', 'required' => 'no'), 
			'shipping_city' => array('name' => 'SHIPTOCITY', 'required' => 'no'), 
			'shipping_state' => array('name' => 'SHIPTOSTATE', 'required' => 'no'), 
			'shipping_postal_code' => array('name' => 'SHIPTOZIP', 'required' => 'no'), 
			'shipping_country_code' => array('name' => 'SHIPTOCOUNTRYCODE', 'required' => 'no'), 
			'shipping_phone_number' => array('name' => 'SHIPTOPHONENUM', 'required' => 'no')*/

		/* Construct and add any items found in this instance */
		/* - MAY NOT BE NECESSARY
		if(!empty($this->ItemsArray))
			{
			// Counter for the total of all the items put together
			$total_items_amount = 0;
			// Go through the items array
			foreach($this->ItemsArray as $key => $value)
				{
				// Get the array of the current item from the main array
				$current_item = $this->ItemsArray[$key];
				// Add it to the request string
				$nvpstr .= "&L_NAME".$key."=".$current_item['name'].
							"&L_NUMBER".$key."=".$current_item['number'].
							"&L_QTY".$key."=".$current_item['quantity'].
							"&L_TAXAMT".$key."=".$current_item['amount_tax'].
							"&L_AMT".$key."=".$current_item['amount'];
				// Add this item's amount to the total current count
				$total_items_amount += ($current_item['amount'] * $current_item['quantity']);
				}
			// Set the amount_items for this instance and ITEMAMT added to the request string
			$this->amount_items = $total_items_amount;
			$nvpstr .= "&ITEMAMT=".urlencode($total_items_amount);
			}
		
		*/
				
		//$arrData = deserialize($objOrder->payment_data, true);
		
		$objRequest = new Request();
		$objRequest->send('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/webscr?cmd=_express-checkout&token=', implode('&', $arrData), 'post');

		//$objRequest->response;
		
		/*
			response array
			'DoDirectPayment' => array(
						'timestamp' => 'TIMESTAMP',
						'correlation_id' => 'CORRELATIONID',
						'ack' => 'ACK',
						'version' => 'VERSION',
						'build' => 'BUILD',
						'avs_code' => 'AVSCODE',
						'cvv2_match' => 'CVV2MATCH',
						'transaction_id' => 'TRANSACTIONID',
						'amount_total' => 'AMT',
						'currency_code' => 'CURRENCYCODE'
						)
				,
		*/
		
		//	LIVE
		// private $API_ENDPOINT = 'https://api-3t.paypal.com/nvp';
		//	SANDBOX
		$API_ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';

		
		if (strlen($arrData['status']) && $arrData['status'] == 'Completed')
		{
			unset($_SESSION['PAYPAL_TIMEOUT']);
			return true;
		}
		
		if (!isset($_SESSION['PAYPAL_TIMEOUT']))
		{
			$_SESSION['PAYPAL_TIMEOUT'] = 60;
		}
		else
		{
			$_SESSION['PAYPAL_TIMEOUT'] = $_SESSION['PAYPAL_TIMEOUT'] - 5;
		}
		
		if ($_SESSION['PAYPAL_TIMEOUT'] === 0)
		{
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'error';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['paypal_processing_failed'];
			return $objTemplate->parse();
		}
	
		
		$objTemplate = new FrontendTemplate('mod_message');
		$objTemplate->type = 'processing';
		$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['paypal_processing'];
		return $objTemplate->parse();
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
			$arrPayment = deserialize($objOrder->payment_data, true);
			
			// Store request data in order for future references
			$arrPayment['POSTSALE'][] = $_POST;
			
			
			$arrData = $objOrder->row();
			$arrData['old_payment_status'] = $GLOBALS['TL_LANG']['MSC']['payment_status_labels'][$arrPayment['status']];
			
			$arrPayment['status'] = $this->Input->post('payment_status');
			$arrData['new_payment_status'] = $GLOBALS['TL_LANG']['MSC']['payment_status_labels'][$arrPayment['status']];
			
			// array('pending','processing','shipped','complete','on_hold', 'cancelled'),
			switch( $arrPayment['status'] )
			{
				case 'Completed':
					break;
					
				case 'Canceled_Reversal':
				case 'Denied':
				case 'Expired':
				case 'Failed':
				case 'Voided':
					$this->Database->prepare("UPDATE tl_iso_orders SET status=? WHERE id=?")->execute('cancelled', $objOrder->id);
					break;
					
				case 'In-Progress':
				case 'Partially_Refunded':
				case 'Pending':
				case 'Processed':
				case 'Refunded':
				case 'Reversed':
					break;
			}
			
			$this->Database->prepare("UPDATE tl_iso_orders SET payment_data=? WHERE id=?")->execute(serialize($arrPayment), $objOrder->id);
			
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
<h2>' . $GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][1] . '</p>
<form id="payment_form" action="https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="charset" value="UTF-8">
<input type="hidden" name="business" value="' . $this->paypal_account . '">
<input type="hidden" name="lc" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '">
<input type="hidden" name="item_name" value="' . $this->paypal_business . '"/>
<input type="hidden" name="amount" value="' . $this->Cart->subTotal . '"/>
<input type="hidden" name="shipping" value="' . $this->Cart->shippingTotal . '">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="' . $this->Isotope->Store->currency . '">
<input type="hidden" name="button_subtype" value="services">
<input type="hidden" name="return" value="' . $this->Environment->url . '/' . $this->addToUrl('step=complete') . '">
<input type="hidden" name="cancel_return" value="' . $this->Environment->url . '/' . $this->addToUrl('step=failed') . '">
<input type="hidden" name="rm" value="1">
<input type="hidden" name="invoice" value="' . $objOrder->order_id . '">

<input type="hidden" name="address_override" value="1">
<input type="hidden" name="first_name" value="' . $this->Cart->billingAddress['firstname'] . '">
<input type="hidden" name="last_name" value="' . $this->Cart->billingAddress['lastname'] . '">
<input type="hidden" name="address1" value="' . $this->Cart->billingAddress['street'] . '">
<input type="hidden" name="zip" value="' . $this->Cart->billingAddress['postal'] . '">
<input type="hidden" name="city" value="' . $this->Cart->billingAddress['city'] . '">
<input type="hidden" name="country" value="' . strtoupper($this->Cart->billingAddress['country']) . '">
<input type="hidden" name="email" value="' . $this->Cart->billingAddress['email'] . '">
<input type="hidden" name="night_phone_c" value="' . $this->Cart->billingAddress['phone'] . '">

<input type="hidden" name="notify_url" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
<input type="' . (strlen($this->button) ? 'image" src="'.$this->button.'" border="0"' : 'submit" value="'.specialchars($GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][2]).'"') . ' alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
$(\'payment_form\').submit();
//--><!]]>
</script>';
	}
	
	public function getAllowedCCTypes()
	{
		return array('mc', 'visa', 'amex', 'discover', 'jcb', 'diners', 'enroute');				
	}
	
}

