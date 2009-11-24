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
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
		if(!$this->authorize_bypass_live_collection)
		{
			//for Authorize.net - this would be where to handle logging response information from the server.
			$authnet_values = array
			(
				"x_login"							=> $this->authorize_login,
				"x_version"							=> $this->getRequestData('x_version'),
				"x_test_request"					=> $this->debug,
				"x_delim_char"						=> $this->authorize_delimiter,
				"x_delim_data"						=> "TRUE",
				"x_url"								=> "FALSE",
				"x_test_request"					=> $this->getRequestData('x_test_request'),
				"x_type"							=> $this->authorize_trans_type,
				"x_method"							=> "CC",
				"x_tran_key"						=> $this->authorize_trans_key,
				"x_card_num"						=> $this->getRequestData('cc_num'),
				"x_exp_date"						=> $this->getRequestData('cc_exp'),
				"x_description"						=> "Order Number " . $objDc->id,
				"x_amount"							=> $this->Cart->grandTotal,
				"x_first_name"						=> $this->getRequestData('x_first_name'),
				"x_last_name"						=> $this->getRequestData('x_last_name'),
				"x_address"							=> $this->getRequestData('x_address'),
				"x_city"							=> $this->getRequestData('x_city'),
				"x_state"							=> $this->getRequestData('x_state'),
				"x_zip"								=> $this->getRequestData('x_zip'),
				"x_company"							=> $this->getRequestData('x_company'),
				"x_email_customer"					=> "FALSE"
			);
	
			foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";
			$ch = curl_init(); 
	
			###  Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
			### $ch = curl_init("https://secure.authorize.net/gateway/transact.dll"); 
			
			curl_setopt($ch, CURLOPT_URL, sprintf('https://%s.authorize.net/gateway/transact.dll', $this->getRequestData('x_url'))); 
			curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
	
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
			$resp = curl_exec($ch); //execute post and get results
			curl_close ($ch);
			
							
			$arrResponses = $this->handleResponse($resp);
	
			foreach(array_keys($arrResponses) as $key)
			{
				$arrReponseLabels[strtolower(standardize($key))] = $key;
			}
		
		
			//FIXME?? - This just doesn't seem like a good way to handle this info...
			$_SESSION['FORM_DATA']['cc_num'] = $this->getRequestData('cc_num');
			$_SESSION['FORM_DATA']['cc_exp'] = $this->getRequestData('cc_exp');
		
			switch($arrResponses['transaction-status'])
			{
				case 'Approved':
					$this->response = 'successful';
					
					return true;
					//$this->redirect($this->addToUrl('step=complete'));
					break;
				case 'Error':
				case 'Declined':
					$this->response = 'failed';
					$this->reason = $arrResponses['reason'];
					
					return false;
					break;
				default:
					$this->response = 'failed';
					$this->reason = $arrResponses['reason'];
					
					return false;
					break;
			}
		
		}else{
	
			//FIXME?? - This just doesn't seem like a good way to handle this info...
			$_SESSION['FORM_DATA']['cc_num'] = $this->getRequestData('cc_num');
			$_SESSION['FORM_DATA']['cc_exp'] = $this->getRequestData('cc_exp');
			$_SESSION['FORM_DATA']['cc_type'] = $this->getRequestData('cc_type');

			//Bypass actual live curl hit, just approve for later processing.
			$this->response = 'successful';
			
		}
		
		return true;
		
		
	}
	
	
	/**
	 * Process post-sale requestion from the Postfinance payment server.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPostSale()
	{
		$this->log('Post-sale request from Authorize.net: ', 'PaymentAuthorizeDotNet postProcessPayment()', TL_ACCESS);
		
		
				
		//"x_email"							=> $this->arrBillingInfo['email']	
		//"x_cardholder_authentication_value"	=> $this->getRequestData('cc_cvv'), - set to on or off eventually.  Higher conversion rates without it in lots of cases.
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
		$this->import('IsotopeCart', 'Cart');
		
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Cart->id);
		$arrAddress = $this->Isotope->getAddress('billing');
		
		
			
		$strTestValue = "false";
		$strCurlUrl = 'secure'; 
		
		if ($this->debug)
		{
			$strCurlUrl = 'test';
			$strTestValue = "true";
		}
		
		$arrData = array
		(
			'currency'		=> $this->Isotope->Store->currency,
			//'SHASign'		=> sha1($objOrder->order_id . ($this->Cart->grandTotal * 100) . $this->Isotope->Store->currency . $this->postfinance_pspid . $this->postfinance_secret),
		);
		
		$this->Database->prepare("UPDATE tl_iso_orders SET payment_data=? WHERE id=?")->execute(serialize($arrData), $objOrder->id);
		if(strlen($this->allowed_cc_types))
		{
			$arrCCTypes = deserialize($this->allowed_cc_types);
		}
		
		
		$strReturn = '
		<input type="hidden" name="x_login" value="' . $this->authorize_login . '">
		<input type="hidden" name="x_url" value="' . $strCurlUrl . '">
		<input type="hidden" name="x_version" value="3.1">
		<input type="hidden" name="x_test_request" value="' . $strTestValue . '">
		<input type="hidden" name="x_delim_char" value="' . $this->authorize_delimiter . '">
		<input type="hidden" name="x_delim_data" value="TRUE">
		<input type="hidden" name="x_type" value="' . $this->authorize_trans_type . '">
		<input type="hidden" name="x_method" value="CC">
		<input type="hidden" name="x_tran_key" value="' . $this->authorize_trans_key . '">
		<input type="hidden" name="x_card_num" value="' . $this->Input->post('cc_num') . '">
		<input type="hidden" name="x_card_type" value="' . $this->Input->post('cc_type') . '">
		<input type="hidden" name="x_exp_date" value="' . $this->Input->post('cc_exp') . '">
		<!--<input type="hidden" name="x_card_code" value="' . $this->Input->post('cc_cvv') . '">-->
		<input type="hidden" name="x_description" value="New Order ID ' . $objOrder->order_id . ($this->debug ? ' ' . $GLOBALS['TL_LANG']['MSC']['testTransaction'] : '') . '">
		<input type="hidden" name="x_amount" value="' . $this->Cart->grandTotal . '">
		<input type="hidden" name="x_first_name" value="' . $arrAddress['firstname'] . '">
		<input type="hidden" name="x_last_name" value="' . $arrAddress['lastname'] . '">
		<input type="hidden" name="x_address" value="' . $arrAddress['street'] . '">
		<input type="hidden" name="x_city" value="' . $arrAddress['city'] . '">
		<input type="hidden" name="x_state" value="' . $arrAddress['state'] . '">
		<input type="hidden" name="x_zip" value="' . $arrAddress['postal'] . '">
		<input type="hidden" name="x_company" value="' . $arrAddress['company'] . '">
		<input type="hidden" name="x_email_customer" value="FALSE">
		<input type="hidden" name="x_email"' . $arrAddress['email'] . '">
		<table cellpadding="0" cellspacing="0" border="0">
		<tbody>
		<tr><td><label for="cc_num">Credit Card Number:</label></td><td><input type="text" name="cc_num" id="ctrl_cc_num" /></td></tr>
		<tr><td><label for="cc_type">Credit Card Type:</label></td><td><select name="cc_type" id="ctrl_cc_type"><option value="" selected>-</option>';
		foreach($arrCCTypes as $type)
		{
			$strReturn .= '<option value="' . $type . '">' . $GLOBALS['ISO_PAY']['cc_types'][$type] . '</option>';
		}
		$strReturn .= '</select></td></tr>
		<tr><td><label for="cc_exp">Credit Card Expiration (mm/yy):</label></td><td><input type="text" name="cc_exp" id="ctrl_cc_exp" /></td></tr></tbody></table>';
		
		return $strReturn;

	}
	
	private function getRequestData($strKey)
	{
		if($this->Input->get($strKey))
		{
			return $this->Input->get($strKey);
		}
					
		return $this->Input->post($strKey);
	}
	
	private function generateResponseString($arrResponses, $arrResponseLabels)
	{
		$responseString .= '<tr><td align="right" colspan="2">&nbsp;</td></tr>';
			
			$showReason = true;
						
			foreach($arrResponses as $k=>$v)
			{
				$value = $v;
				
				switch($k)
				{
					case 'transaction-status':
						switch($v)
						{
							case "Declined":
							case "Error":
								$value = $this->addAlert($v); 
								$showReason = true;
								break;
							default:
								$value = "<strong>" . $v . "</strong>";
								break;
						}
						break;
					case 'reason':
						if(!$showReason)
						{
							continue;
						}
						
						$value = $this->addAlert($v); //. "<br /><a href=\"" . $this->session['infoPage'] . "\"><strong>Click here to review and correct your order</strong></a>";
						$this->strReason = $value;
					case 'grand-total':
						$value = $v;
						break;
				}	
				
				$responseString .= '<tr><td align="right" width="150">' . $arrResponseLabels[$k] . ':&nbsp;&nbsp;</td><td>' . $value . '</td></tr>';
				
			}
			
			return $responseString;
	}
	
	private function handleResponse($resp)
	{
		
		$resp = str_replace('"', '', $resp);
		
		$arrResponseString = explode(",",$resp);
		
		$i=1;
		
		$arrFieldsToDisplay = array(1, 4, 7, 9, 10, 11, 14, 15, 16, 17, 18, 19, 20, 22, 23, 24);	//Dynamic Later
		
		foreach($arrResponseString as $currResponseString)
		{
				if(empty($currResponseString)){
					$i++;
					continue; //$pstr_trimmed="NO VALUE RETURNED";
				}
				
				if(in_array($i, $arrFieldsToDisplay))
				{
					$pstr_trimmed = $currResponseString;
					
					switch($i)
					{
						
						case 1:
							$ftitle = "Transaction Status";
									
							$fval="";
							if($pstr_trimmed=="1"){
								$fval="Approved";
							}elseif($pstr_trimmed=="2"){
								$fval="Declined";
							}elseif($pstr_trimmed=="3"){
								$fval="Error";
							}
							break;
						
						case 4:
							$ftitle = "Reason";
							$fval = $pstr_trimmed;
							break;
							
						case 7:
							$ftitle = "Transaction ID";
							$fval = $pstr_trimmed;
							break;
							
						case 9:
							$ftitle = "Service";
							$fval = $pstr_trimmed;
							break;
							
						case 10:
							$ftitle = "Grand Total";
							$fval = $pstr_trimmed;
							break;
							
						case 11:
							$ftitle = "Payment Method";
							$fval = ($pstr_trimmed=="CC" ? "Credit Card" : "Other");
							break;
						
						case 14:	
							$ftitle = "First Name";
							$fval = $pstr_trimmed;
							break;
						
						case 15:	
							$ftitle = "Last Name";
							$fval = $pstr_trimmed;
							break;
							
						case 16:	
							$ftitle = "Company Name";
							$fval = $pstr_trimmed;
							break;
							
						case 17:	
							$ftitle = "Billing Address";
							$fval = $pstr_trimmed;
							break;
							
						case 18:	
							$ftitle = "City";
							$fval = $pstr_trimmed;
							break;
							
						case 19:	
							$ftitle = "State";
							$fval = $pstr_trimmed;
							break;
							
						case 20:	
							$ftitle = "Zip";
							$fval = $pstr_trimmed;
							break;
							
						case 22:	
							$ftitle = "Phone";
							$fval = $pstr_trimmed;
							break;
							
						case 23:	
							$ftitle = "Fax";
							$fval = $pstr_trimmed;
							break;
							
						case 24:	
							$ftitle = "Email";
							$fval = $pstr_trimmed;
							break;
							
						default:
							break;
					}
			
					$arrResponse[strtolower(standardize($ftitle))] = $fval;
				}
	
			$i++;
		}
	
		return $arrResponse;
	}


}

