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
		
	public function __get($strKey)
	{
		switch( $strKey )
		{
			// Make sure at least one credit card is available
			case 'available':
				if (parent::__get($strKey) && is_array($this->allowed_cc_types) && count($this->allowed_cc_types))
				{
					return true;
				}
				return false;
				break;
				
			default:
				return parent::__get($strKey);
		}
	}
	
		
	/**
	 * Process payment on confirmation page.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		$this->import('IsotopeCart', 'Cart');
		
		$fields = '';
		
		//for Authorize.net - this would be where to handle logging response information from the server.
		$authnet_values = array
		(
			"x_login"							=> $this->authorize_login,
			"x_version"							=> '3.1',
			"x_test_request"					=> ($this->debug ? 'true' : 'false'),
			"x_delim_char"						=> $this->authorize_delimiter,
			"x_delim_data"						=> "TRUE",
			"x_url"								=> "FALSE",
			"x_type"							=> $this->authorize_trans_type,
			"x_method"							=> "CC",
			"x_tran_key"						=> $this->authorize_trans_key,
			"x_card_num"						=> $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'],
			"x_exp_date"						=> $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_exp'],
			"x_description"						=> "Order Number " . $objDc->id,
			"x_amount"							=> $this->Cart->grandTotal,
			"x_first_name"						=> $this->Cart->billingAddress['firstname'],
			"x_last_name"						=> $this->Cart->billingAddress['lastname'],
			"x_address"							=> $this->Cart->billingAddress['street'],
			"x_city"							=> $this->Cart->billingAddress['city'],
			"x_state"							=> $this->Cart->billingAddress['state'],
			"x_zip"								=> $this->Cart->billingAddress['postal'],
			"x_company"							=> $this->Cart->billingAddress['company'],
			"x_email_customer"					=> "FALSE"
		);

		if($this->authorize_require_ccv)
		{
			$authnet_values["x_card_code"] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_ccv'];
		}

		foreach( $authnet_values as $key => $value )
		{
			$fields .= "$key=" . urlencode( $value ) . "&";
		}
		
		$objRequest = new Request();
		$objRequest->send('https://secure.authorize.net/gateway/transact.dll', $fields, 'post');
		
		$arrResponses = $this->handleResponse($objRequest->response);
		
/*
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, ('https://secure.authorize.net/gateway/transact.dll'));
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$resp = curl_exec($ch); //execute post and get results
		curl_close ($ch);

		$arrResponses = $this->handleResponse($resp);
*/
		
		foreach(array_keys($arrResponses) as $key)
		{
			$arrReponseLabels[strtolower(standardize($key))] = $key;
		}
	
	
		//FIXME?? - This just doesn't seem like a good way to handle this info...
		
		//Save Auth.net-specific data
	
		switch($arrResponses['transaction-status'])
		{
			case 'Approved':
				$arrPaymentData = array();
				$arrSet = array();
				
				$this->response = 'successful';
				
				$strTransactionId = (string)$arrResponses['transaction-id'];
				
				if(!strlen($strTransactionId))
				{
					$strTransactionId = '0';
				}
				
				$this->import('IsotopeCart','Cart');
				
				$strCCNum = rtrim($this->Input->post('cc_num'));
							
				$arrPaymentData['x_trans_id'] = $strTransactionId;
				
				$arrPaymentData['cc-last-four'] = substr($strCCNum, strlen($strCCNum) - 4, 4);
				
				//commit the transaction id and cart id to a new order.
				$arrSet['payment_data'] = serialize($arrPaymentData);
				
				$arrSet['cart_id'] = $this->Cart->id;

				$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")
										   ->limit(1)
										   ->execute($this->Cart->id);
				
				if(!$objOrder->numRows)
				{
					$this->Database->prepare("INSERT INTO tl_iso_orders %s")
								   ->set($arrSet)
								   ->execute();				
				}
				else
				{
					$this->Database->prepare("UPDATE tl_iso_orders %s WHERE id=?")
								   ->set($arrSet)
								   ->execute($objOrder->id);
				}
				
				return true;
				break;
				
			default:
				$_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error'] = $arrResponses['reason'];
				$this->redirect($this->addToUrl('step=payment'));
				break;
		}
		
	}
		
	
	public function paymentForm($objCheckoutModule)
	{
		$strBuffer = '';
		$arrPayment = $this->Input->post('payment');
		$arrCCTypes = deserialize($this->allowed_cc_types);
		
		$arrFields = array
		(
			'cc_num' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_num'],
				'inputType'		=> 'text',
				'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tableless'=>true),
			),
			'cc_type' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_type'],
				'inputType'		=> 'select',
				'options'		=> $arrCCTypes,
				'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tableless'=>true),
				'reference'		=> &$GLOBALS['TL_LANG']['CCT'],
			),
			'cc_exp' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp'],
				'inputType'		=> 'text',
				'eval'			=> array('mandatory'=>true, 'tableless'=>true),
			),
			'cc_ccv' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_ccv'],
				'inputType'		=> 'text',
				'eval'			=> array('mandatory'=>true, 'tableless'=>true)						
			),
		);
				
		foreach( $arrFields as $field => $arrData )
		{
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, 'payment['.$this->id.']['.$field.']', $_SESSION['CHECKOUT_DATA']['payment'][$this->id][$field]));
			
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && $arrPayment['module'] == $this->id)
			{
				$objWidget->validate();
				
				if ($objWidget->hasErrors())
				{
					$objCheckoutModule->doNotSubmit = true;
				}
			}
			elseif ($objWidget->mandatory && !strlen($objWidget->value))
			{
				$objCheckoutModule->doNotSubmit = true;
			}
			
			$strBuffer .= $objWidget->parse();
		}
		
		if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && $arrPayment['module'] == $this->id && !$objCheckoutModule->doNotSubmit)
		{
			$strCard = $this->validateCreditCard($arrPayment[$this->id]['cc_num']);
			
			if ($strCard === false)
			{
				$strBuffer = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['cc_num'] . '</p>' . $strBuffer;
				$objCheckoutModule->doNotSubmit = true;
			}
			elseif ($strCard != $arrPayment[$this->id]['cc_type'])
			{
				$strBuffer = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['cc_match'] . '</p>' . $strBuffer;
				$objCheckoutModule->doNotSubmit = true;
			}
		}
		
		if (strlen($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error']))
		{
			$strBuffer = '<p class="error">' . $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error'] . '</p>' . $strBuffer;
			unset($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error']);
		}
		
		return $strBuffer;
	}
	
	
	public function checkoutReview()
	{
		$type = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_type'];
		$num = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'];
		
		$strCard = implode(' ', str_split((substr($num, 0, 2) . str_repeat('*', (strlen($num)-6)) . substr($num, -4)), 4));
		
		return sprintf('%s<br />%s: %s', $this->label, $GLOBALS['TL_LANG']['CCT'][$type], $strCard);
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

	public function getAllowedCCTypes()
	{
		return array('mc', 'visa', 'amex', 'discover', 'jcb', 'diners', 'enroute');				
	}
}

