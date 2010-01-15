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
		$this->import('IsotopeCart', 'Cart');
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Cart->id);
		
		$arrData = deserialize($objOrder->payment_data, true);
		
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
		
		// Reload page every 5 seconds and check if payment was successful
		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="5,' . $this->Environment->url . '/' . $this->Environment->request . '">';
		
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

		$objRequest = new Request();
		$objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/webscr?cmd=_express-checkout&token=' . ), implode('&', $arrData), 'post');
		
		/* TO INTEGRATE
			//converting NVPResponse to an Associative Array
		$nvpResArray = $this->deformatNVP($response);
		$nvpReqArray = $this->deformatNVP($nvpreq);
		$_SESSION['nvpReqArray'] = $nvpReqArray;
		
		
			*************
			if NO SUCCESS
			*************
			
		if (curl_errno($ch)) 
			{
			// moving to display page to display curl errors

			$_SESSION['curl_error_no'] = curl_errno($ch) ;
			$_SESSION['curl_error_msg'] = curl_error($ch);
			
			$this->_error				= true;
			$this->ack					= 'Failure';
			$this->_error_type			= 'curl';
			$this->_error_date			= date("Y-m-d H:i:s");
			$this->_error_code			= curl_errno($ch);
			$this->_error_short_message	= 'There was an error trying to contact the PayPal servers. (curl error) See long message for details.';
			$this->_error_long_message	= curl_error($ch);
			
			return false;
			} 
	
			*************
			if SUCCESS
			*************
		
		else 
			{
			//closing the curl
			curl_close($ch);
			}
		
		return $nvpResArray;
		 END TO INTEGRATE */
		
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
	
		public function DoDirectPayment()
		{
		// urlencode the needed variables
		$this->urlencodeVariables();
		
		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		$nvpstr = $this->generateNVPString('DoDirectPayment');
		
		/* Construct and add any items found in this instance */
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
		
		// decode the variables incase we still require access to them in our program
		$this->urldecodeVariables();
		
		/* Make the API call to PayPal, using API signature.
		   The API response is stored in an associative array called $this->Response */
		$this->Response = $this->hash_call("DoDirectPayment", $nvpstr);
		
		// TODO: Add error handling for the hash_call
		
		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if(strtoupper($this->Response["ACK"]) != "SUCCESS" AND strtoupper($this->Response["ACK"]) != "SUCCESSWITHWARNING")
			{
			$this->Error['TIMESTAMP']		= @$this->Response['TIMESTAMP'];
			$this->Error['CORRELATIONID']	= @$this->Response['CORRELATIONID'];
			$this->Error['ACK']				= $this->Response['ACK'];
			$this->Error['ERRORCODE']		= $this->Response['L_ERRORCODE0'];
			$this->Error['SHORTMESSAGE']	= $this->Response['L_SHORTMESSAGE0'];
			$this->Error['LONGMESSAGE']		= $this->Response['L_LONGMESSAGE0'];
			$this->Error['SEVERITYCODE']	= $this->Response['L_SEVERITYCODE0'];
			$this->Error['VERSION']			= @$this->Response['VERSION'];
			$this->Error['BUILD']			= @$this->Response['BUILD'];
			
			// TODO: Error codes for AVSCODE and CVV@MATCH
			
			$this->_error				= true;
			$this->_error_ack			= $this->Response['ACK'];
			$this->ack					= 'Failure';
			$this->_error_type			= 'paypal';
			$this->_error_date			= $this->Response['TIMESTAMP'];
			$this->_error_code			= $this->Response['L_ERRORCODE0'];
			$this->_error_short_message	= $this->Response['L_SHORTMESSAGE0'];
			$this->_error_long_message	= $this->Response['L_LONGMESSAGE0'];
			$this->_error_severity_code	= $this->Response['L_SEVERITYCODE0'];
			$this->_error_version		= @$this->Response['VERSION'];
			$this->_error_build			= @$this->Response['BUILD']; 
			
			return false;
			}
			/*
			*************
			if SUCCESS
			*************
			*/
		elseif(strtoupper($this->Response["ACK"]) == 'SUCCESS' OR strtoupper($this->Response["ACK"]) == 'SUCCESSWITHWARNING')
			{
			/*
			Take the response variables and put them into the local class variables
			*/
			foreach($this->ResponseFieldsArray['DoDirectPayment'] as $key => $value)
				$this->$key = $this->Response[$value];
			
			return true;
			}
		}
	
	
	
	
	
	function SetExpressCheckout()
		{
		// TODO: Add error handling prior to trying to make PayPal calls. ie: missing amount_total or RETURN_URL
		
		// urlencode the needed variables
		$this->urlencodeVariables();
		
		/* Construct the parameter string that describes the PayPal payment
			the varialbes were set in the web form, and the resulting string
			is stored in $nvpstr
			*/
		$nvpstr = $this->generateNVPString('SetExpressCheckout');
				
		// decode the variables incase we still require access to them in our program
		$this->urldecodeVariables();
		
		/* Make the call to PayPal to set the Express Checkout token
			If the API call succeded, then redirect the buyer to PayPal
			to begin to authorize payment.  If an error occured, show the
			resulting errors
			*/
		$this->Response = $this->hash_call("SetExpressCheckout", $nvpstr);
		
		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if(strtoupper($this->Response["ACK"]) != "SUCCESS")
			{
			$this->Error['TIMESTAMP']		= @$this->Response['TIMESTAMP'];
			$this->Error['CORRELATIONID']	= @$this->Response['CORRELATIONID'];
			$this->Error['ACK']				= $this->Response['ACK'];
			$this->Error['ERRORCODE']		= $this->Response['L_ERRORCODE0'];
			$this->Error['SHORTMESSAGE']	= $this->Response['L_SHORTMESSAGE0'];
			$this->Error['LONGMESSAGE']		= $this->Response['L_LONGMESSAGE0'];
			$this->Error['SEVERITYCODE']	= $this->Response['L_SEVERITYCODE0'];
			$this->Error['VERSION']			= @$this->Response['VERSION'];
			$this->Error['BUILD']			= @$this->Response['BUILD'];
			
			$this->_error				= true;
			$this->_error_ack			= $this->Response['ACK'];
			$this->ack					= 'Failure';
			$this->_error_type			= 'paypal';
			$this->_error_date			= $this->Response['TIMESTAMP'];
			$this->_error_code			= $this->Response['L_ERRORCODE0'];
			$this->_error_short_message	= $this->Response['L_SHORTMESSAGE0'];
			$this->_error_long_message	= $this->Response['L_LONGMESSAGE0'];
			$this->_error_severity_code	= $this->Response['L_SEVERITYCODE0'];
			$this->_error_version		= @$this->Response['VERSION'];
			$this->_error_build			= @$this->Response['BUILD']; 
			
			return false;
			/*
			$_SESSION['reshash']=$this->Response;
			$location = "APIError.php";
			header("Location: $location");
			*/
			}
		/*
			*************
			if SUCCESS
			*************
			*/
		elseif(strtoupper($this->Response["ACK"]) == 'SUCCESS')
			{
			/*
			Take the response variables and put them into the local class variables
			*/
			foreach($this->ResponseFieldsArray['SetExpressCheckout'] as $key => $value)
				$this->$key = $this->Response[$value];
			
			return true;
			}
		}
	
	function SetExpressCheckoutSuccessfulRedirect()
		{
		// Redirect to paypal.com here
		$token = urlencode($this->Response["TOKEN"]);
		$paypal_url = $this->PAYPAL_URL.$token;
		header("Location: ".$paypal_url);
		}
	
	
	
	
	function GetExpressCheckoutDetails()
		{
		// TODO: Add error handling prior to PayPal calls. ie: missing TOKEN
		
		/* At this point, the buyer has completed in authorizing payment
			at PayPal.  The script will now call PayPal with the details
			of the authorization, incuding any shipping information of the
			buyer.  Remember, the authorization is not a completed transaction
			at this state - the buyer still needs an additional step to finalize
			the transaction
			*/

		 /* Build a second API request to PayPal, using the token as the
			ID to get the details on the payment authorization
			*/
		/* Construct the parameter string that describes the PayPal payment
			the varialbes were set in the web form, and the resulting string
			is stored in $nvpstr
			*/
		$nvpstr = $this->generateNVPString('GetExpressCheckoutDetails');

		 /* Make the API call and store the results in an array.  If the
			call was a success, show the authorization details, and provide
			an action to complete the payment.  If failed, show the error
			*/
		$this->Response = $this->hash_call("GetExpressCheckoutDetails", $nvpstr);
		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if(strtoupper($this->Response["ACK"]) != "SUCCESS")
			{
			$this->Error['TIMESTAMP']		= @$this->Response['TIMESTAMP'];
			$this->Error['CORRELATIONID']	= @$this->Response['CORRELATIONID'];
			$this->Error['ACK']				= $this->Response['ACK'];
			$this->Error['ERRORCODE']		= $this->Response['L_ERRORCODE0'];
			$this->Error['SHORTMESSAGE']	= $this->Response['L_SHORTMESSAGE0'];
			$this->Error['LONGMESSAGE']		= $this->Response['L_LONGMESSAGE0'];
			$this->Error['SEVERITYCODE']	= $this->Response['L_SEVERITYCODE0'];
			$this->Error['VERSION']			= @$this->Response['VERSION'];
			$this->Error['BUILD']			= @$this->Response['BUILD'];
			
			$this->_error				= true;
			$this->_error_ack			= $this->Response['ACK'];
			$this->ack					= 'Failure';
			$this->_error_type			= 'paypal';
			$this->_error_date			= $this->Response['TIMESTAMP'];
			$this->_error_code			= $this->Response['L_ERRORCODE0'];
			$this->_error_short_message	= $this->Response['L_SHORTMESSAGE0'];
			$this->_error_long_message	= $this->Response['L_LONGMESSAGE0'];
			$this->_error_severity_code	= $this->Response['L_SEVERITYCODE0'];

			$this->_error_version		= @$this->Response['VERSION'];
			$this->_error_build			= @$this->Response['BUILD']; 
			
			return false;
			/*
			$_SESSION['reshash']=$this->Response;
			$location = "APIError.php";
			header("Location: $location");
			*/
			}
		/*
			***********
			if SUCCESS
			***********
			*/
		elseif(strtoupper($this->Response["ACK"]) == 'SUCCESS')
			{
			/*
			Take the response variables and put them into the local class variables
			*/
			foreach($this->ResponseFieldsArray['GetExpressCheckoutDetails'] as $key => $value)
				$this->$key = $this->Response[$value];
			
			return true;
			}
		
		}
	
	
	
	
	function DoExpressCheckoutPayment()
		{
		// TODO: Error checking. ie: we require a token and payer_id here
		
		// urlencode the needed variables
		$this->urlencodeVariables();
		
		/* Construct the parameter string that describes the PayPal payment
			the varialbes were set in the web form, and the resulting string
			is stored in $nvpstr
			*/
		$nvpstr = $this->generateNVPString('DoExpressCheckoutPayment');
		
		/* Construct and add any items found in this instance */
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
			$nvpstr .= "&ITEMAMT=".$total_items_amount;
			}

		 /* Make the call to PayPal to finalize payment
			If an error occured, show the resulting errors
			*/
		$this->Response = $this->hash_call("DoExpressCheckoutPayment", $nvpstr);
		
		// decode the variables incase we still require access to them in our program
		$this->urldecodeVariables();
		
		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if(strtoupper($this->Response["ACK"]) != "SUCCESS")
			{
			$this->Error['TIMESTAMP']		= @$this->Response['TIMESTAMP'];
			$this->Error['CORRELATIONID']	= @$this->Response['CORRELATIONID'];
			$this->Error['ACK']				= $this->Response['ACK'];
			$this->Error['ERRORCODE']		= $this->Response['L_ERRORCODE0'];
			$this->Error['SHORTMESSAGE']	= $this->Response['L_SHORTMESSAGE0'];
			$this->Error['LONGMESSAGE']		= $this->Response['L_LONGMESSAGE0'];
			$this->Error['SEVERITYCODE']	= $this->Response['L_SEVERITYCODE0'];
			$this->Error['VERSION']			= @$this->Response['VERSION'];
			$this->Error['BUILD']			= @$this->Response['BUILD'];
			
			$this->_error				= true;
			$this->_error_ack			= $this->Response['ACK'];
			$this->ack					= 'Failure';
			$this->_error_type			= 'paypal';
			$this->_error_date			= $this->Response['TIMESTAMP'];
			$this->_error_code			= $this->Response['L_ERRORCODE0'];
			$this->_error_short_message	= $this->Response['L_SHORTMESSAGE0'];
			$this->_error_long_message	= $this->Response['L_LONGMESSAGE0'];
			$this->_error_severity_code	= $this->Response['L_SEVERITYCODE0'];
			$this->_error_version		= @$this->Response['VERSION'];
			$this->_error_build			= @$this->Response['BUILD']; 
			
			return false;
			/*
			$_SESSION['reshash']=$this->Response;
			$location = "APIError.php";
			header("Location: $location");
			*/
			}
		/*
			*************
			if SUCCESS
			*************
			*/
		elseif(strtoupper($this->Response["ACK"]) == 'SUCCESS')
			{
			/*
			Take the response variables and put them into the local class variables
			*/
			foreach($this->ResponseFieldsArray['DoExpressCheckoutPayment'] as $key => $value)
				$this->$key = $this->Response[$value];
			
			return true;
			}
		}
	
	
	
	
	function GetTransactionDetails()
		{
		/* Construct the parameter string that describes the PayPal payment
			the varialbes were set in the web form, and the resulting string
			is stored in $nvpstr
			*/
		$nvpstr = $this->generateNVPString('GetTransactionDetails');
		
		/* Make the API call to PayPal, using API signature.
		   The API response is stored in an associative array called $resArray */
		$this->Response = $this->hash_call("GetTransactionDetails", $nvpstr);
		
		/* Next, collect the API request in the associative array $reqArray
		   as well to display back to the browser.
		   Normally you wouldnt not need to do this, but its shown for testing */
		
		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if(strtoupper($this->Response["ACK"]) != "SUCCESS")
			{
			$this->Error['TIMESTAMP']		= @$this->Response['TIMESTAMP'];
			$this->Error['CORRELATIONID']	= @$this->Response['CORRELATIONID'];
			$this->Error['ACK']				= $this->Response['ACK'];
			$this->Error['ERRORCODE']		= $this->Response['L_ERRORCODE0'];
			$this->Error['SHORTMESSAGE']	= $this->Response['L_SHORTMESSAGE0'];
			$this->Error['LONGMESSAGE']		= $this->Response['L_LONGMESSAGE0'];
			$this->Error['SEVERITYCODE']	= $this->Response['L_SEVERITYCODE0'];
			$this->Error['VERSION']			= @$this->Response['VERSION'];
			$this->Error['BUILD']			= @$this->Response['BUILD'];
			
			$this->_error				= true;
			$this->_error_ack			= $this->Response['ACK'];
			$this->ack					= 'Failure';
			$this->_error_type			= 'paypal';
			$this->_error_date			= $this->Response['TIMESTAMP'];
			$this->_error_code			= $this->Response['L_ERRORCODE0'];
			$this->_error_short_message	= $this->Response['L_SHORTMESSAGE0'];
			$this->_error_long_message	= $this->Response['L_LONGMESSAGE0'];
			$this->_error_severity_code	= $this->Response['L_SEVERITYCODE0'];
			$this->_error_version		= @$this->Response['VERSION'];
			$this->_error_build			= @$this->Response['BUILD']; 
			
			return false;
			/*
			$_SESSION['reshash']=$this->Response;
			$location = "APIError.php";
			header("Location: $location");
			*/
			}
		/*
			*************
			if SUCCESS
			*************
			*/
		elseif(strtoupper($this->Response["ACK"]) == 'SUCCESS')
			{
			/*
			Take the response variables and put them into the local class variables
			*/
			foreach($this->ResponseFieldsArray['GetTransactionDetails'] as $key => $value)
				$this->$key = $this->Response[$value];
			
			$this->getItems($this->Response);
			
			return true;
			}
		}
	
	
	
	
	function RefundTransaction()
		{
		/* Construct the parameter string that describes the PayPal payment
			the varialbes were set in the web form, and the resulting string
			is stored in $nvpstr
			*/
		$nvpstr = $this->generateNVPString('RefundTransaction');
		
		/* Make the API call to PayPal, using API signature.
		   The API response is stored in an associative array called $resArray */
		$this->Response = $this->hash_call("RefundTransaction", $nvpstr);
		
		/* Next, collect the API request in the associative array $reqArray
		   as well to display back to the browser.
		   Normally you wouldnt not need to do this, but its shown for testing */
		
		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if(strtoupper($this->Response["ACK"]) != "SUCCESS")
			{
			$this->Error['TIMESTAMP']		= @$this->Response['TIMESTAMP'];
			$this->Error['CORRELATIONID']	= @$this->Response['CORRELATIONID'];
			$this->Error['ACK']				= $this->Response['ACK'];
			$this->Error['ERRORCODE']		= $this->Response['L_ERRORCODE0'];
			$this->Error['SHORTMESSAGE']	= $this->Response['L_SHORTMESSAGE0'];
			$this->Error['LONGMESSAGE']		= $this->Response['L_LONGMESSAGE0'];
			$this->Error['SEVERITYCODE']	= $this->Response['L_SEVERITYCODE0'];
			$this->Error['VERSION']			= @$this->Response['VERSION'];
			$this->Error['BUILD']			= @$this->Response['BUILD'];
			
			$this->_error				= true;
			$this->_error_ack			= $this->Response['ACK'];
			$this->ack					= 'Failure';
			$this->_error_type			= 'paypal';
			$this->_error_date			= $this->Response['TIMESTAMP'];
			$this->_error_code			= $this->Response['L_ERRORCODE0'];
			$this->_error_short_message	= $this->Response['L_SHORTMESSAGE0'];
			$this->_error_long_message	= $this->Response['L_LONGMESSAGE0'];
			$this->_error_severity_code	= $this->Response['L_SEVERITYCODE0'];
			$this->_error_version		= @$this->Response['VERSION'];
			$this->_error_build			= @$this->Response['BUILD']; 
			
			return false;
			/*
			$_SESSION['reshash']=$this->Response;
			$location = "APIError.php";
			header("Location: $location");
			*/
			}
		/*
			*************
			if SUCCESS
			*************
			*/
		elseif(strtoupper($this->Response["ACK"]) == 'SUCCESS')
			{
			/*
			Take the response variables and put them into the local class variables
			*/
			foreach($this->ResponseFieldsArray['RefundTransaction'] as $key => $value)
				$this->$key = $this->Response[$value];
			
			$this->getItems($this->Response);
			
			return true;
			}
		}
	
	

	
		
	/**
	  * hash_call: Function to perform the API call to PayPal using API signature
	  * @methodName is name of API  method.
	  * @nvpStr is nvp string.
	  * returns an associtive array containing the response from the server.
	*/
	private function hash_call($methodName, $nvpStr)
		{
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->API_ENDPOINT);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		//if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	    //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
		if($this->USE_PROXY)
			curl_setopt ($ch, CURLOPT_PROXY, $this->PROXY_HOST.":".$this->PROXY_PORT); 
	
		//NVPRequest for submitting to server
		$nvpreq = "METHOD=".urlencode($methodName)."&VERSION=".urlencode($this->VERSION)."&PWD=".urlencode($this->API_PASSWORD).
				"&USER=".urlencode($this->API_USERNAME)."&SIGNATURE=".urlencode($this->API_SIGNATURE).$nvpStr;
		
		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
	
		//getting response from server
		$response = curl_exec($ch);
	
		//converting NVPResponse to an Associative Array
		$nvpResArray = $this->deformatNVP($response);
		$nvpReqArray = $this->deformatNVP($nvpreq);
		$_SESSION['nvpReqArray'] = $nvpReqArray;
		
		/*
			*************
			if NO SUCCESS
			*************
			*/
		if (curl_errno($ch)) 
			{
			// moving to display page to display curl errors

			$_SESSION['curl_error_no'] = curl_errno($ch) ;
			$_SESSION['curl_error_msg'] = curl_error($ch);
			
			$this->_error				= true;
			$this->ack					= 'Failure';
			$this->_error_type			= 'curl';
			$this->_error_date			= date("Y-m-d H:i:s");
			$this->_error_code			= curl_errno($ch);
			$this->_error_short_message	= 'There was an error trying to contact the PayPal servers. (curl error) See long message for details.';
			$this->_error_long_message	= curl_error($ch);
			
			return false;
			} 
		/*
			*************
			if SUCCESS
			*************
			*/
		else 
			{
			//closing the curl
			curl_close($ch);
			}
		
		return $nvpResArray;
		}
		
		
		
		/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
		  * It is usefull to search for a particular key and displaying arrays.
		  * @nvpstr is NVPString.
		  * @nvpArray is Associative Array.
		  */
		private function deformatNVP($nvpstr)
			{
			$intial=0;
			$nvpArray = array();
			
			while(strlen($nvpstr))
				{
				//postion of Key
				$keypos= strpos($nvpstr,'=');
				//position of value
				$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
		
				/*getting the Key and Value values and storing in a Associative Array*/
				$keyval=substr($nvpstr,$intial,$keypos);
				$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
				//decoding the respose
				$nvpArray[urldecode($keyval)] =urldecode( $valval);
				$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
				}
				
			return $nvpArray;
			}
		
		
		
		
		/** This function will add an item to the itemArray for use in doDirectPayment and doExpressCheckoutPayment
		  */
		public function addItem($name, $number, $quantity, $amount_tax, $amount)
			{
			$new_item =  array(
					'name' => $name, 
					'number' => $number, 
					'quantity' => $quantity, 
					'amount_tax' => $amount_tax, 
					'amount' => $amount);
			
			$this->ItemsArray[] = $new_item;
			
			// TODO: Should recalculate and set $this->amount_items after every new item is added. Or is this done on each request?
			}
		
		
		
		private function getItems($passed_response)
			{
			// Clear any current items
			$this->ItemsArray = '';
			
			// Get the items if there are any
			// Start this off by checking for a first item
			if(!empty($passed_response['L_NAME0']) OR !empty($passed_response['L_NUMBER0']) OR !empty($passed_response['L_QTY0']))
				{
				$i = 0;
				// Start a loop to get all the items (up to 200)
				// We'll break out of it if we stop finding items
				while($i < 200)
					{
					// One of the Name, Number, and Qty fields may be empty, so check all of them
					//   and if any of them are filled, then we have an item
					if(!empty($passed_response['L_NAME'.$i]) OR !empty($passed_response['L_NUMBER'.$i]) OR !empty($passed_response['L_QTY'.$i]))
						{
						$new_item =  array(
								'name' => $passed_response['L_NAME'.$i], 
								'number' => $passed_response['L_NUMBER'.$i], 
								'quantity' => $passed_response['L_QTY'.$i], 
								'amount_tax' => $passed_response['L_TAXAMT'.$i], 
								'amount' => $passed_response['L_AMT'.$i]);
						
						$this->ItemsArray[] = $new_item;
						$i++;
						}
					else
						break;
					}
				}
			}
		
		
		
		private function generateNVPString($type)
			{
			$temp_nvp_str = '';
			// Go through the selected RequestFieldsArray and create the request string
			//    based on whether the field is required or filled
			// TODO: return error if required field is empty?
			foreach($this->RequestFieldsArray[$type] as $key => $value)
				{
				if($value['required'] == 'yes')
					$temp_nvp_str .= '&'.$value['name'].'='.$this->$key;
				elseif(!empty($this->$key))
					$temp_nvp_str .= '&'.$value['name'].'='.$this->$key;
				}
			return $temp_nvp_str;
			}
		
		
		
		/** This function encodes all applicable variables for transport to PayPal
		  */
		private function urlencodeVariables()
			{
			// Decode all specified variables
			$this->payment_type			= urlencode($this->payment_type);
			
			$this->email		= urlencode($this->email);
			$this->first_name			= urlencode($this->first_name);
			$this->last_name			= urlencode($this->last_name);
			$this->credit_card_type		= urlencode($this->credit_card_type);
			$this->credit_card_number	= urlencode($this->credit_card_number);
			$this->expire_date_month		= urlencode($this->expire_date_month);
			
			// Month must be padded with leading zero
			$this->expire_date_month	= urlencode(str_pad($this->expire_date_month, 2, '0', STR_PAD_LEFT));
			
			$this->expire_date_year	= urlencode($this->expire_date_year);
			$this->cvv2_code		= urlencode($this->cvv2_code);
			$this->address1			= urlencode($this->address1);
			$this->address2			= urlencode($this->address2);
			$this->city				= urlencode($this->city);
			$this->state			= urlencode($this->state);
			$this->postal_code		= urlencode($this->postal_code);
			$this->country_code		= urlencode($this->country_code);
			
			$this->currency_code	= urlencode($this->currency_code);
			$this->ip_address		= urlencode($this->ip_address);
			
			$this->shipping_name			= urlencode($this->shipping_name);
			$this->shipping_address1		= urlencode($this->shipping_address1);
			$this->shipping_address2		= urlencode($this->shipping_address2);
			$this->shipping_city			= urlencode($this->shipping_city);
			$this->shipping_state			= urlencode($this->shipping_state);
			$this->shipping_postal_code		= urlencode($this->shipping_postal_code);
			$this->shipping_country_code	= urlencode($this->shipping_country_code);
			$this->shipping_phone_number			= urlencode($this->shipping_phone_number);
			
			$this->amount_total		= urlencode($this->amount_total);
			$this->amount_shipping	= urlencode($this->amount_shipping);
			$this->amount_tax		= urlencode($this->amount_tax);
			$this->amount_handling	= urlencode($this->amount_handling);
			$this->amount_items		= urlencode($this->amount_items);
			
			$this->token		= urlencode($this->token);
			$this->payer_id		= urlencode($this->payer_id);
			
	
			if(!empty($this->ItemsArray))
				{
				// Go through the items array
				foreach($this->ItemsArray as $key => $value)
					{
					// Get the array of the current item from the main array
					$current_item = $this->ItemsArray[$key];
					// Encode everything
					// TODO: use a foreach loop instead
					$current_item['name'] = urlencode($current_item['name']);
					$current_item['number'] = urlencode($current_item['number']);
					$current_item['quantity'] = urlencode($current_item['quantity']);
					$current_item['amount_tax'] = urlencode($current_item['amount_tax']);
					$current_item['amount'] = urlencode($current_item['amount']);
					// Put the encoded array back in the item array (replaces previous array)
					$this->ItemsArray[$key] = $current_item;
					}
				}
			}
		
		/** This function DEcodes all applicable variables for use in application/database
		  */
		private function urldecodeVariables()
			{
			// Decode all specified variables
			$this->payment_type			= urldecode($this->payment_type);
			
			$this->email		= urldecode($this->email);
			$this->first_name			= urldecode($this->first_name);
			$this->last_name			= urldecode($this->last_name);
			$this->credit_card_type		= urldecode($this->credit_card_type);
			$this->credit_card_number	= urldecode($this->credit_card_number);
			$this->expire_date_month		= urldecode($this->expire_date_month);
			
			// Month must be padded with leading zero
			$this->expire_date_month	= urldecode(str_pad($this->expire_date_month, 2, '0', STR_PAD_LEFT));
			
			$this->expire_date_year	= urldecode($this->expire_date_year);
			$this->cvv2_code		= urldecode($this->cvv2_code);
			$this->address1			= urldecode($this->address1);
			$this->address2			= urldecode($this->address2);
			$this->city				= urldecode($this->city);
			$this->state			= urldecode($this->state);
			$this->postal_code		= urldecode($this->postal_code);
			$this->country_code		= urldecode($this->country_code);
			
			$this->currency_code	= urldecode($this->currency_code);
			$this->ip_address		= urldecode($this->ip_address);
			
			$this->shipping_name				= urldecode($this->shipping_name);
			$this->shipping_address1			= urldecode($this->shipping_address1);
			$this->shipping_address2			= urldecode($this->shipping_address2);
			$this->shipping_city				= urldecode($this->shipping_city);
			$this->shipping_state				= urldecode($this->shipping_state);
			$this->shipping_postal_code			= urldecode($this->shipping_postal_code);
			$this->shipping_country_code		= urldecode($this->shipping_country_code);
			$this->shipping_phone_number	= urldecode($this->shipping_phone_number);
			
			$this->amount_total		= urldecode($this->amount_total);
			$this->amount_shipping	= urldecode($this->amount_shipping);
			$this->amount_tax		= urldecode($this->amount_tax);
			$this->amount_handling	= urldecode($this->amount_handling);
			$this->amount_items		= urldecode($this->amount_items);
			
			$this->token		= urldecode($this->token);
			$this->payer_id		= urldecode($this->payer_id);
			
			
			if(!empty($this->ItemsArray))
				{
				// Go through the items array
				foreach($this->ItemsArray as $key => $value)
					{
					// Get the array of the current item from the main array
					$current_item = $this->ItemsArray[$key];
					// Decode everything
					// TODO: use a foreach loop instead
					$current_item['name'] = urldecode($current_item['name']);
					$current_item['number'] = urldecode($current_item['number']);
					$current_item['quantity'] = urldecode($current_item['quantity']);
					$current_item['amount_tax'] = urldecode($current_item['amount_tax']);
					$current_item['amount'] = urldecode($current_item['amount']);
					// Put the decoded array back in the item array (replaces previous array)
					$this->ItemsArray[$key] = $current_item;
					}
				}
			}
}

