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
 * @author	   Blair Winans <blair@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class PaymentAuthorizeDotNet extends IsotopePayment
{

	/**
	 * Status
	 *
	 * @access protected
	 * @var string
	 */
	protected $strStatus;

	/**
	 * Reason
	 *
	 * @access protected
	 * @var string
	 */
	protected $strReason;


	/**
	 * Get an object property
	 *
	 * @access public
	 * @param string
	 * @param mixed
	 */
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
		//We have already done the Authorization - go to Complete step
		if($this->authorize_trans_type =='AUTH_ONLY')
			return true;

		//Otherwise we capture payment
		// Get the current order
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->execute($this->Isotope->Cart->id);
		//$arrPaymentData = deserialize($objOrder->payment_data);
		return $this->authCapturePayment($objOrder->id, $objOrder->grandTotal, true);
	}


	/**
	 * Generate payment authorization form
	 * NOTE:  Will always AUTH_ONLY at this step for PCI Compliance. Capture will take place at process step.
	 *
	 * @access public
	 * @param object
	 * @return string
	 */
	public function paymentForm($objModule)
	{
		$strBuffer = '';
		$arrPayment = $this->Input->post('payment');
		$arrCCTypes = deserialize($this->allowed_cc_types);

		$intStartYear = (integer)date('Y', time()); //2-digit year

		//Build years array - Going forward 7 years
		for($i=0;$i<=7;$i++)
			$arrYears[] = (string)$intStartYear+$i;

		//Build form fields
		$arrFields = array
		(
			'card_accountNumber'	=> array
			(
				'label'				=> &$GLOBALS['TL_LANG']['ISO']['cc_num'],
				'inputType'			=> 'text',
				'eval'				=> array('mandatory'=>true, 'tableless'=>true),
			),
			'card_cardType' 		=> array
			(
				'label'				=> &$GLOBALS['TL_LANG']['ISO']['cc_type'],
				'inputType'			=> 'select',
				'options'			=> $arrCCTypes,
				'eval'				=> array('mandatory'=>true, 'tableless'=>true),
				'reference'			=> &$GLOBALS['ISO_LANG']['CCT'],
			),
			'card_expirationMonth' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp_month'],
				'inputType'		=> 'select',
				'options'		=> array('01','02','03','04','05','06','07','08','09','10','11','12'),
				'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'includeBlankOption'=>true)
			),
			'card_expirationYear'  => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp_year'],
				'inputType'		=> 'select',
				'options'		=> $arrYears,
				'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'includeBlankOption'=>true)
			)
		);

		if($this->requireCCV)
		{
			$arrFields['card_cvNumber'] = array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_ccv'],
				'inputType'		=> 'text',
				'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'class'=>'ccv')
			);
		}

		foreach( $arrFields as $field => $arrData )
		{
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, 'payment['.$field.']'));

			$objWidget->value = $_SESSION['CHECKOUT_DATA']['payment'][$field];

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == 'payment_form' && $arrPayment['module']==$this->id)
			{
				$objWidget->validate();

				if ($objWidget->hasErrors())
				{
					$objModule->doNotSubmit = true;
				}
			}
			elseif ($objWidget->mandatory && !strlen($objWidget->value))
			{
				$objModule->doNotSubmit = true;
			}

			//PCI Compliance - Mask CC Values
			if($field=='card_accountNumber' && strlen($objWidget->value))
			{
				$objWidget->value = $this->maskCC($objWidget->value);
			}
			if($field=='card_cvNumber' && strlen($objWidget->value))
			{
				$objWidget->value = '****';
			}

			$strBuffer .= $objWidget->parse();
		}

		if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && !$objModule->doNotSubmit && $arrPayment['module']==$this->id && !$_SESSION['CHECKOUT_DATA']['payment']['request_lockout'])
		{
			// Get the current order, review page will create the data
			$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->execute($this->Isotope->Cart->id);

			$_SESSION['CHECKOUT_DATA']['payment']['request_lockout'] = true;
			
			$blnAuthCapture = $this->authCapturePayment($objOrder->id, $this->Isotope->Cart->grandTotal, false);

			if($blnAuthCapture)
			{
					unset($_SESSION['CHECKOUT_DATA']['responseMsg']);
					$_SESSION['CHECKOUT_DATA']['payment']['card_accountNumber'] = $this->maskCC($arrPayment['card_accountNumber']); //PCI COMPLIANCE - MASK THE CC DATA
					$_SESSION['CHECKOUT_DATA']['payment']['card_cvNumber'] = '****';
					$_SESSION['CHECKOUT_DATA']['payment']['success'] = true;
			}
			else
			{
				$objModule->doNotSubmit = true;
				$_SESSION['CHECKOUT_DATA']['responseMsg'] = sprintf("Transaction failure. Transaction Status: %s, Reason: %s", $this->strStatus, $this->strReason);
			}

		}

		return '
<h2>' . $this->label . '</h2>'.
($_SESSION['CHECKOUT_DATA']['responseMsg'] == '' ? '' : '<p class="error message">'. $_SESSION['CHECKOUT_DATA']['responseMsg'] . '</p>').$strBuffer;

	}

	/**
	 * Generate the backend POS terminal
	 *
	 * @param int
	 * @access public
	 * @return string
	 */
	public function backendInterface($intOrderId)
	{
		$arrOrderInfo = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")
										   ->limit(1)
										   ->execute($intOrderId)
										   ->fetchAssoc();

		$this->Input->setGet('uid', $arrOrderInfo['uniqid']);
		$objModule = new ModuleIsotopeOrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));

		$strOrderDetails = $objModule->generate(true);

		$arrPaymentData = deserialize($arrOrderInfo['payment_data'], true);

		//Get the authorize.net configuration data
		$objAIMConfig = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE type=?")
														->execute('authorizedotnet');
		if($objAIMConfig->numRows < 1)
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}

		//Code specific to Authorize.net!
		$objTemplate = new BackendTemplate('be_pos_terminal');

		if($objAIMConfig->numRows > 0)
		{
			$this->authorize_delimiter 	= $objAIMConfig->authorize_delimiter;
			$this->authorize_login 		= $objAIMConfig->authorize_login;
			$this->authorize_trans_key 	= $objAIMConfig->authorize_trans_key;
			$this->debug 				= $objAIMConfig->debug;
			$this->new_order_status 	= $objAIMConfig->new_order_status;
			$this->authorize_trans_type = $objAIMConfig->authorize_trans_type;
		}


		if ($this->Input->post('FORM_SUBMIT') == 'be_pos_terminal' && $arrPaymentData['transaction-id']!=="0")
		{
			$blnAuthCapture = $this->authCapturePayment($arrOrderInfo['id'], $arrPaymentData['transaction-id'], $arrOrderInfo['grandTotal'], true);

			$strResponse = '<p class="tl_info">' . sprintf("Transaction Status: %s, Reason: %s", $this->strStatus, $this->strReason) . '</p>';
		}

		$return = '<div id="tl_buttons">
<input type="hidden" name="FORM_SUBMIT" value="be_pos_terminal" />
<a href="'.ampersand(str_replace('&key=payment', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
<h2 class="sub_headline">' . $GLOBALS['ISO_LANG']['PAY']['authorizedotnet'][0] . (!$arrPaymentData['transaction-id'] || $arrPaymentData['transaction-id']=="0" ? ' - ' . 'Test Transaction' : '') . '</h2>
<div class="tl_formbody_edit">
<div class="tl_tbox block">';
$return .= ($strResponse ? $strResponse : '');
$return .= $strOrderDetails;
$return .= '</div></div>';
		if($arrOrderInfo['status']!='complete'){
			$return .= '<div class="tl_formbody_submit"><div class="tl_submit_container">';
			$return .= '<input type="submit" class="submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']) . '" /></div></div>';
		}

		$objTemplate->orderReview = $return;
		$objTemplate->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);

		return $objTemplate->parse();
	}


	/**
	 * Authorize or capture the payment
	 *
	 * @param int
	 * @param float
	 * @param array
	 * @param bool
	 * @access public
	 * @return bool
	 */
	public function authCapturePayment($intOrderId, $fltOrderTotal, $blnCapture=false)
	{
		//Gather Order data and set IsotopeOrder object
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $intOrderId))
		{
			$objOrder->uniqid		= uniqid($this->Isotope->Config->orderPrefix, true);
			$objOrder->cart_id		= $this->Isotope->Cart->id;
			$objOrder->findBy('id', $objOrder->save());
		}

		$strLineItems = '';
		$arrBilling = array();
		$arrShipping = array();
		$arrProducts = array();
		$arrPaymentInfo = array();
		
		//Gather product and address data depending on FE(Cart) or BE(Order)
		if(TL_MODE=='FE')
		{
			$arrBilling = $this->Isotope->Cart->billingAddress;
			$arrShipping = $this->Isotope->Cart->shippingAddress;
			$arrProducts = $this->Isotope->Cart->getProducts();
		}
		else
		{
			$arrBilling = $objOrder->billingAddress;
			$arrShipping = $objOrder->shippingAddress;
			$arrProducts =  $objOrder->getProducts();
		}
		if(count($arrProducts))
		{
			foreach($arrProducts as $objProduct)
			{
				$arrItemData = array();
				$arrItemData = array($objProduct->id, $objProduct->name, substr($objProduct->description,0,100), $objProduct->quantity_requested, $objProduct->price, ($objProduct->tax_class ? 'Y' : 'N'));
				$arrLineItems[] = implode('<|>',$arrItemData);
			}

			$strLineItems .= implode('&',$arrLineItems);
		}

		//Authorization type
		$strAuthType = $blnCapture ? 'PRIOR_AUTH_CAPTURE' : 'AUTH_ONLY';

		//Get Address Data
		$arrBillingSubdivision = explode('-', $arrBilling['subdivision']);
		$arrShippingSubdivision = explode('-', $arrShipping['subdivision']);
		
		//Set up basic request fields required by all transactions
		$authnet_values_default = array
		(
			"x_version"							=> '3.1',
			"x_login"							=> $this->authorize_login,
			"x_tran_key"						=> $this->authorize_trans_key,
			"x_type"							=> $strAuthType,
			"x_delim_char"						=> $this->authorize_delimiter,
			"x_delim_data"						=> "TRUE",
			"x_relay_response" 					=> "FALSE",
			"x_amount"							=> $fltOrderTotal	
			//"x_test_request"					=> ($this->debug ? 'true' : 'false'),
		);
		
		switch($strAuthType)
		{
			case 'AUTH_ONLY':
				$authnet_values_authonly = array(		
					"x_url"								=> "FALSE",					
					"x_description"						=> "Order Number " . $this->Isotope->Config->orderPrefix . $objOrder->id,
					"x_invoice_num"						=> $objOrder->id,
					"x_first_name"						=> $arrBilling['firstname'],
					"x_last_name"						=> $arrBilling['lastname'],
					"x_company"							=> $arrBilling['company'],
					"x_address"							=> $arrBilling['street_1']."\n".$arrBilling['street_2']."\n".$arrBilling['street_3'],
					"x_city"							=> $arrBilling['city'],
					"x_state"							=> $arrBillingSubdivision[1],
					"x_zip"								=> $arrBilling['postal'],
					"x_email_customer"					=> "FALSE",
					"x_email"							=> $arrBilling['email'],
					"x_country"							=> $arrBilling['country'],
					"x_phone"							=> $arrBilling['phone'],
					"x_ship_to_first_name"				=> $arrShipping['firstname'],
					"x_ship_to_last_name"				=> $arrShipping['lastname'],
					"x_ship_to_company"					=> $arrShipping['company'],
					"x_ship_to_address"					=> $arrShipping['street_1']."\n".$arrShipping['street_2']."\n".$arrShipping['street_3'],
					"x_ship_to_city"					=> $arrShipping['city'],
					"x_ship_to_state"					=> $arrShippingSubdivision[1],
					"x_ship_to_zip"						=> $arrShipping['postal'],
					"x_ship_to_country"					=> $arrShipping['country'],
				);
				
				$authnet_values = array_merge($authnet_values_default,$authnet_values_authonly);			
				break;
			case 'PRIOR_AUTH_CAPTURE':
				$arrTransactionData = deserialize($objOrder->payment_data,true);				
				$authnet_values = array_merge($authnet_values_default,array("x_trans_id" => $arrTransactionData['transaction-id']));
				break;
			default:
				break;				
		}
				
						
		if(!$blnCapture) //Gather CC Data from post
		{
			$arrPaymentInput = $this->Input->post('payment');
		
			$authnet_values["x_method"] 	= "CC";
			$authnet_values["x_card_num"]	= $arrPaymentInput['card_accountNumber'];
			$authnet_values["x_exp_date"]	= ($arrPaymentInput['card_expirationMonth'].substr($arrPaymentInput['card_expirationYear'], 2, 2));
			
			if($this->requireCCV)
			{
				$authnet_values["x_card_code"] = $arrPaymentInput['card_cvNumber'];
			}	
			
			$arrPaymentInfo["x_card_num"]	= $this->maskCC($arrData['card_accountNumber']); //PCI COMPLIANCE - MASK THE CC DATA
			$arrPaymentInfo["x_card_type"]	= $GLOBALS['ISO_LANG']['CCT'][$arrData['card_cardType']];		
		}
		
		foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";

		$fieldsFinal = rtrim($fields, '&');

		$objRequest = new Request();

		$objRequest->send('https://'.($this->debug ? 'test' : 'secure').'.authorize.net/gateway/transact.dll', $fieldsFinal, 'post');

		$arrResponses = $this->handleResponse($objRequest->response);
		$arrResponseCodes = $this->getResponseCodes($objRequest->response);

		foreach(array_keys($arrResponses) as $key)
		{
			$arrReponseLabels[strtolower(standardize($key))] = $key;
		}

		$objOrder->transaction_response = $arrResponses['transaction-status'];
		$objOrder->transaction_response_code = $arrResponseCodes['response_code'];

		$this->loadLanguageFile('payment');
		$this->strStatus = $arrResponses['transaction-status'];
		$this->strReason = $GLOBALS['TL_LANG']['MSG']['authorizedotnet'][$arrResponseCodes['response_type']][$arrResponseCodes['response_code']];

		if(!$blnCapture)
		{
			switch($arrResponses['transaction-status'])
			{
				case 'Approved':
					$objOrder->status = $this->new_order_status;
					$blnFail = false;
					break;
				default:
					$objOrder->status = 'on_hold';
					$blnFail = true;
					break;
	
			}
		}
						
		//Update payment data AKA Response Data. Transaction ID will not be saved during test mode.
		$arrOrderPaymentData = deserialize($objOrder->payment_data,true);
		$arrPaymentInfo = (count($arrOrderPaymentData)) ? array_merge($arrResponses, $arrOrderPaymentData) : $arrResponses;
				
		$objOrder->payment_data = serialize($arrPaymentInfo);
		
		$objOrder->save();
		
		//unlock the payment submit
		$_SESSION['CHECKOUT_DATA']['payment']['request_lockout'] = false;
		
		if($blnFail)
		{
			$this->log(sprintf("Transaction failure. Transaction Status: %s, Reason: %s", $this->strStatus, $this->strReason), 'PaymentAuthorizeDotNet capturePayment()', TL_ERROR);
			return false;
		}

		return true;
	}


	/**
	 * Get the primary response codes used to generate a message
	 *
	 * @param string
	 * @access private
	 * @return array
	 */
	private function getResponseCodes($strResp)
	{
		$strResp = str_replace('"', '', $strResp);
		$arrResponseString = explode($this->authorize_delimiter, $strResp);
		$arrResponseCodes = array
		(
			'response_type'	=> $arrResponseString[0],
			'response_code'	=> $arrResponseString[2]
		);

		return $arrResponseCodes;
	}


	/**
	 * Convert the response string from Auth.net to an array of values
	 *
	 * @param string
	 * @access private
	 * @return array
	 */
	private function handleResponse($strResp)
	{
		$strResp = str_replace('"', '', $strResp);
		$arrResponseString = explode($this->authorize_delimiter, $strResp);
		$i=1;
		$arrFieldsToDisplay = array(1, 2, 3, 4, 5, 7, 9, 10, 11, 14, 15, 16, 17, 18, 19, 20, 22, 23, 24, 47);	//Dynamic Later

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
						case 3:	//response reason code.
							$ftitle = "Reason Code";
							$fval = $pstr_trimmed;
							break;
						case 4:
							$ftitle = "Reason";
							$fval = $pstr_trimmed;
							break;
						case 5:
							$ftitle = "Authorization Code";
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
						case 47:
							$ftitle = "Amount";
							$fval = $pstr_trimmed;
						default:
							break;
					}

					$arrResponse[strtolower(standardize($ftitle))] = $fval;
				}

			$i++;
		}

		return $arrResponse;
	}


	/**
	 * Return allowed CC types
	 *
	 * @access public
	 * @return array
	 */
	public function getAllowedCCTypes()
	{
		return array('mc', 'visa', 'amex', 'discover', 'jcb', 'diners', 'enroute');
	}


	/**
	 * Mask CC values for PCI Compliance
	 *
	 * @access public
	 * @param string
	 * @return string
	 */
	public function maskCC($strNumber)
	{
		return str_pad(substr($strNumber, -4), strlen($strNumber), '*', STR_PAD_LEFT);
	}
}