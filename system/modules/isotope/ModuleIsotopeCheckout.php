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
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    IsotopeBase 
 * @license    Commercial 
 * @filesource
 */


/**
 * Class ModuleIsotopeCheckout
 *
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    Controller
 */
class ModuleIsotopeCheckout extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_mod_checkout_default';
	
	/**
	 *
	 */
	protected $blnShowLoginOptions = false;
	
	/**
	 * Recall product data, if db has been updated with new information.
	 * @param boolean
	 */
	//protected $blnRecallProductData = false;

	protected $strStepTemplateBaseName = 'iso_checkout_';
	
	//protected $intCartId;
	
	protected $arrStoreSettings = array();
	
	protected $arrSession = array();
	
	protected $strOrderStatus;
	
	protected $fltOrderTotal = 0.00;
	
	protected $fltOrderSubtotal = 0.00;
	
	protected $fltOrderShippingTotal = 0.00;
	
	protected $fltOrderTaxTotal = 0.00;
	
	protected $intBillingAddressId;
	
	protected $intShippingAddressId;	

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE CHECKOUT ###';

			return $objTemplate->parse();
		}

		// Fallback template
		if (!strlen($this->iso_checkout_layout))
		{
			$this->iso_checkout_layout = 'iso_mod_checkout_default';
		}

		//$this->strTemplate = $this->iso_checkout_layout;
		
		if($this->store_id < 1)
		{
			return '<i>' . $GLOBALS['TL_LANG']['ERR']['noStoreIdFound'] . '</i>';		
		}
		
		$this->arrStoreSettings = $this->getCurrentStoreConfigById($this->store_id);
		
		if(!sizeof($this->arrStoreSettings))
		{
			return '<i>' . $GLOBALS['TL_LANG']['ERR']['noStoreIdFound'] . '</i>';
		}
		
		$this->import('FrontendUser','User');
		
		$this->strUserId = $this->User->id;
				
		if(!$this->strUserId || !FE_USER_LOGGED_IN)
		{
			$this->blnShowLoginOptions = true;
		}
		
		$this->intCartId = $this->userCartExists($this->strUserId);
		
		if(!$this->intCartId)
		{
			//redirect away from checkout.
			$this->redirect($this->Environment->base);
			
		}	
		
		$this->arrSession = $this->Session->getData();
		
		$this->arrJumpToValues = $this->getStoreJumpToValues($this->store_id);	//Deafult keys are "product_reader", "shopping_cart", and "checkout"
		
		return parent::generate();
			
		
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{	
		//$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEPS'] = array('login','shipping_information','billing_information','shipping_method','billing_method','order_review');

		//global $objPage;
		
		//If the user is not logged in, we will give them a choice in terms of login options
		
		
		/* 1. Next Step 
		   2. Build widgets from fields used for given step.
		   3. Render out step html via current step key
		   4. Add step html to steps array
		   */
		// Set template
		//if (strlen($this->memberTpl))
		//{
		$this->Template = new FrontendTemplate($this->strTemplate);
		//}
		
		$action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);

		$this->Template->action = $action;		
		$this->Template->formId = 'iso_mod_checkout';
		$this->Template->formSubmit = "iso_mod_checkout";
		$this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
		$this->Template->rowLast = 'row_' . (count($this->editable) + 1) . ((($i % 2) == 0) ? ' odd' : ' even');

		$intBillingAddressId = ($this->Input->post('billing_address') ? $this->Input->post('billing_address') : $_SESSION['FORM_DATA']['billing_address']);
		
		if($intBillingAddressId > 0)
		{
			//echo $intBillingAddressId;
			$this->intBillingAddressId = $intBillingAddressId;
		}
		
		$intShippingAddressId = ($this->Input->post('shipping_address') ? $this->Input->post('shipping_address') : $_SESSION['FORM_DATA']['shipping_address']);

		if($intShippingAddressId > 0)
		{
			//echo $intBillingAddressId;
			$this->intShippingAddressId = $intShippingAddressId;
		}

		foreach($GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEPS'] as $step)
		{
			if(!$this->blnShowLoginOptions && $step=='login')
			{
				continue;
			}

			// needed here for shipping calculations.					
			$this->fltOrderSubtotal = $this->calculateOrderSubtotal($this->intCartId, $this->strUserId);

			//Specific actions for the given step
			//Each step gets resources from some table somewhere.  It may be the address book, it may be the payment method's payment fields
			//
			switch($step)
			{
			
				case 'login':
					$arrSteps[] = array
					(
						'editEnabled' 	=> false,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$step],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$step],
						'fields' 		=> $this->getLoginInterface(),
						'useFieldset' 	=> false
					);										
					break;
				
				case 'billing_information':
					if(!FE_USER_LOGGED_IN)
					{
						break;
					}
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$step],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$step],
						'fields' 		=> $this->generateAddressWidget($this->strUserId, $step, 'billing_address'),
//$this->getCurrentStepWidgets($step, $this->store_id, 'tl_address_book'),
						'useFieldset' 	=> true
					);
										
					break;
				
				case 'shipping_information':
					if(!FE_USER_LOGGED_IN)
					{
						break;
					}

					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$step],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$step],
						'fields' 		=> $this->generateAddressWidget($this->strUserId, $step, 'shipping_address'),	//$this->getCurrentStepWidgets($step, $this->store_id, 'tl_address_book'),
						'useFieldset' 	=> true
					);
					break;
				
				case 'shipping_method':
					if(!FE_USER_LOGGED_IN)
					{
						break;
					}
					
					//blnLoadDataContainer is set to "false" because we do not gather our widget from those fields. Instead we statically define
					//the fields for now that are used.
					//$arrFieldData = $this->generateRequiredFieldData($step);
					//$this->getCurrentStepWidgets($step, $this->store_id, 'tl_shipping_methods', false, $arrFieldData);
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$step],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$step],
						'useFieldset' 	=> true,
						'fields' 		=> $this->getShippingModulesInterface(deserialize($this->iso_shipping_modules))
						//'fields' 	=> $this->getCurrentStepWidgets($step, $this->store_id, 'tl_shipping_methods', false, $arrFieldData)
					);
					break;
				
				
				case 'payment_method':
					if(!FE_USER_LOGGED_IN)
					{
						break;
					}
					
					//blnLoadDataContainer is set to "false" because we do not gather our widget from those fields. Instead we statically define
					//the fields for now that are used.
					//$arrFieldData = $this->generateRequiredFieldData($step);
					//$this->getCurrentStepWidgets($step, $this->store_id, 'tl_payment_methods', false, $arrFieldData);
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$step],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$step],
						'useFieldset' 	=> true,
						'fields' 		=> $this->getPaymentModuleInterface(deserialize($this->iso_payment_modules))
					);
					break;
				
				case 'order_review':
					//if(!FE_USER_LOGGED_IN)
					//{
						break;
					//}
					//$session = $this->arrSession->getData('ISO_CHECKOUT');	
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> false,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$step],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$step],
						'useFieldset' 	=> true
						//'fields' 	=> $this->getCurrentStepWidgets($step, $this->store_id, 'tl_address_book')
					);
					
					//Take destination zip code, calc tax based on that and shipping based on order total and destination zip
					//$this->calculateTax();
					//$this->calculateShippingTotal();
					//
					//$objOrder->tax ...
					//$objOrder->shipping_cost ...
					//$objOrder->subtotal ...
					//$objOrder->total ...
					//= $total cost to be posted to payment gateway
					break;
				
				default:
					break;
			}
		}
				
		$this->Template->checkoutSteps = $arrSteps;			
		if(FE_USER_LOGGED_IN)
		{
			$this->Template->submitButton = '<div class="submit_container"><input type="submit" class="submit" value="<?php echo $this->slabel; ?>" /></div>';		}

	}
	
	protected function getLoginInterface()
	{		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'login');
		
		$objTemplate->loginModule = '{{insert_module::' . $this->arrStoreSettings['checkout_login_module'] . '}}';
				
		return $objTemplate->parse();	
	}
	
	protected function getBillingInformationInterface()
	{
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'billing_information');
		
		//an array of addresses to select
		//$objTemplate->addressOptions = $this->getAddressOptions($this->strUserId);
		
		//Form to add a new address
		$objTemplate->createNewAddressFields = $this->getCurrentStepWidgets($step, $this->store_id, 'tl_address_book');
		
		return $objTemplate->parse();
	}
	/*
	
	private function getAddressOptions($intUserId)
	{
		$objAddresses = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=?")
									   ->execute($intUserId);
		
		if($objAddresses->numRows < 1)
		{
			return array();
		}
		
		$arrAddresses = $objAddresses->fetchAllAssoc();
	
		$strClass = $GLOBALS['TL_FFL']['radio'];
						
		//arrData is fields parameters.									
		$arrData = array
		(
			'inputType'		=> 'radio',
			'options'		=> array_values($arrAddresses['id']),
			'eval'			=> '',
			'reference'		=> ''
		);		
									
		$objWidget = new $strClass($this->prepareForWidget($arrData, $field, $this->User->$field));
		
		$objWidget->generate();
		
		foreach($arrAddresses as $address)
		{
			$addressString[$address['id']] .= $address['firstname']
		
		}
	
	} */
	
	
	protected function getShippingModulesInterface($arrModuleIds)
	{
		$arrShippingModules = array();
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'shipping_method');
						
		if(!sizeof($arrModuleIds))
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noShippingModules'] . '</i>';
		}
		
		$arrShippingModules = $this->getShippingServicesAndTiers($arrModuleIds);
			
		//var_dump($arrShippingModules);
		//exit;
		
		$arrShippingMethods = $this->calculateShippingCost($arrShippingModules, $this->intCartId, $this->strUserId);
		
				
		if(!sizeof($arrShippingMethods))
		{
			$objTemplate->noShippingMethods = $GLOBALS['TL_LANG']['MSC']['noShippingMethods'];
		}else{
			//array('title' => 'UPS Ground', 'cost' => 8);
			$objTemplate->shippingMethods = $arrShippingMethods;
		}
		
		return $objTemplate->parse();	
	}
	
	protected function getPaymentModuleInterface($arrModuleIds)
	{
		$arrPaymentModules = array();
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'payment_method');
				
		if(!sizeof($arrModuleIds))
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}

		//Get rendered module html by payment option.
		$objTemplate->paymentMethods = $this->getPaymentModules($arrModuleIds);
	
		return $objTemplate->parse();
	}
	
	protected function writeOrder($arrPaymentInfo)
	{
		$session = $this->Session->getData();
		
		$arrSet = array
		(
			
			'pid'					=> $this->strUserId,
			'tstamp'				=> time(),
			'store_id'				=> $this->intStoreId,
			'order_subtotal'		=> $this->fltOrderSubtotal,
			'order_tax' 			=> $this->fltOrderTaxTotal,
			'order_shipping_cost'	=> $this->fltOrderShippingTotal,
			'source_cart_id'		=> $this->intCartId,
			'billing_address_id'	=> $this->intBillingAddressId,
			'shipping_address_id'	=> $this->intShippingAddressId,
			'shipping_method'		=> 'ups_ground',
			'status'				=> 'pending'
		);
				
		$objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")
								  ->limit(1)
								  ->execute($this->strUserId);
		
		foreach($arrPaymentInfo as $k=>$v)
		{
			$arrSet[$k] = $v;
		
		}
		
					
		$objInsert = $this->Database->prepare("INSERT INTO tl_iso_orders %s")
					   		->set($arrSet)
					   		->execute();
		
		$arrData[] = array
		(
			'label'	=> 'Order Id: ',
			'value'	=> $objInsert->insertId
		);
		
		$arrData[] = array
		(
			'label' => 'Order Total: ',
			'value' => $this->fltOrdertotal
		);
		
		$arrData[] = array
		(
			'label' => 'User Id: ',
			'value' => $this->strUserId
		);
		
		$arrData[] = array
		(
			'label' => 'Order Comments',
			'value' => ''
		);
		
		/*$arrData[] = array
		(
			'label' => '',
			'value' => ''
		);
		
		$arrData[] = array
		(
			'label' => '',
			'value' => ''
		);*/
		
		$this->sendAdminNotification($this->strUserId, $objInsert->insertId, $arrData);
		
		$this->emailCustomer($objUser->email, $objUser->firstname);			   
		
		return true;
	
	}
	
	
	/**
	 * send an email confirmation to customers
	 *
	 * @param string
	 * @param string
	 * @return null
	 */
	protected function emailCustomer($strEmail, $strCustomerName)
	{

		$objEmail = new Email();
		
		$strData = sprintf($GLOBALS['TL_LANG']['MSC']['message_new_order_customer_thank_you'], $strCustomerName, $GLOBALS['TL_ADMIN_EMAIL']);
				
		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['subject_new_order_customer_thank_you'], $GLOBALS['TL_LANG']['MSC']['store_title']);
		$objEmail->text = $strData;
		$objEmail->sendTo($strEmail);
		
		return true;
	}
	
	
	/**
	 * Send an admin notification e-mail
	 * @param integer
	 * @param array
	 */
	protected function sendAdminNotification($strUserId, $intOrderId, $arrData)
	{
	
		$objEmail = new Email();

		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['subject_new_order_admin_notify'], $intOrderId, $this->Environment->host); 

		$strData = "\n\n";

		// Add order details
		foreach ($arrData as $row)
		{
						
			$strData .= $row['label'] . $row['value'] . "\n";						

		}

		$objEmail->text = sprintf($GLOBALS['TL_LANG']['MSC']['message_new_order_admin_notify'], $intOrderId, $strData . "\n") . "\n";
		$objEmail->sendTo($GLOBALS['TL_ADMIN_EMAIL']);

		$this->log('New order ID ' . $intOrderId . ') has been placed by user id ' . $strUserId, 'ModuleIsotopeCheckout sendAdminNotification()', TL_ACCESS);
		
		return true;
	}
	
		
	/**
	 * For now, specific to authorize.net
	 *
	 */
	protected function getPaymentModules($arrModuleIds)
	{
			
		foreach($arrModuleIds as $module)
		{
			
			$objPaymentModuleData = $this->Database->prepare("SELECT name, authorizeConfiguration FROM tl_module WHERE id=?")
												   ->limit(1)
												   ->execute($module);
			if($objPaymentModuleData->numRows < 1)
			{
				return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
			}
			
			//Get the authorize.net configuration data			
			$objAIMConfig = $this->Database->prepare("SELECT * FROM tl_authorize WHERE id=?")
															->limit(1)
															->execute($objPaymentModuleData->authorizeConfiguration);
			if($objAIMConfig->numRows < 1)
			{
				return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
			}
		
			
			//$arrParams[$module] = $objPaymentModuleConfiguration->fetchAllAssoc();			
		
			//Code specific to Authorize.net!
			$objTemplate = new FrontendTemplate('mod_aim_checkout');
							
			if($objAIMConfig->numRows > 0)
			{
				
				$delimResponse = ($objAIMConfig->delimResponse==1 ? "TRUE" : "FALSE");
				$delimChar = $objAIMConfig->delimChar;
				$loginID = $objAIMConfig->loginID;
				$transKey = $objAIMConfig->transKey;
				$transType = $objAIMConfig->transType;
				$status = ($objAIMConfig->status=="test" ? "TRUE" : "FALSE");
				
				//var_dump($status);
			}
			
			//Get tax info
			$arrAggregateSetData = $this->getCartProductsByCartId($this->intCartId, $this->strUserId);
		
			if(!sizeof($arrAggregateSetData))
			{
				$arrAggregateSetData = array();
			}
			
			$arrProductData = $this->getProductData($arrAggregateSetData, array('product_price','tax_class'), 'product_price');

			$arrTaxInfo = $this->calculateTax($arrProductData);	
			
						
			if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout')
			{
				$arrBillingAddress = $this->getSelectedBillingAddress($this->intBillingAddressId);
			
				$authnet_values = array(
					"x_login"				=> $loginID,
					"x_version"				=> $GLOBALS['TL_LANG']['MSC']['gatewayVersion'],
					"x_test_request"		=> $status,
					"x_delim_char"			=> ",",
					"x_delim_data"			=> $delimResponse,
					"x_url"					=> "FALSE",
					"x_type"				=> $transType,
					"x_method"				=> "CC",
					"x_tran_key"			=> $transKey,
					"x_relay_response"		=> "FALSE",
					"x_card_num"			=> $this->Input->post('x_card_num'),
					"x_exp_date"			=> $this->Input->post('x_exp_date'),
					"x_cardholder_authentication_value"	=> $this->Input->post('x_cardholder_authentication_value'),
					"x_description"			=> "Test Transaction",
					"x_amount"				=> number_format($this->fltOrderTotal, 2),
					"x_first_name"			=> $arrBillingAddress['firstname'],
					"x_last_name"			=> $arrBillingAddress['lastname'],
					"x_address"				=> $arrBillingAddress['address'],
					"x_city"				=> $arrBillingAddress['city'],
					"x_state"				=> $arrBillingAddress['state'],
					"x_zip"					=> $arrBillingAddress['postal'],
					"x_company"				=> $arrBillingAddress['company'],
					"x_email_customer"		=> "TRUE",
					"x_email"				=> $arrBillingAddress['email']
				);
				
							
				$arrPaymentInfo = array('order_tax' => $this->fltOrderTaxTotal, 'cc_num' => $this->Input->post('x_card_num'), 'cc_exp' => $this->Input->post('x_exp_date'), 'cc_cvv' => $this->Input->post('x_cardholder_authentication_value'), 'order_comments' => $this->Input->post('order_comments'), 'billing_address_id'=>$this->intBillingAddressId, 'shipping_address_id'=>$this->intShippingAddressId, 'gift_message' => $this->Input->post('gift_message'), 'gift_wrap' => $this->Input->post('gift_wrap'));
												
				if($this->writeOrder($arrPaymentInfo))
				{	
					//Update quantities for gift registry items if applicable.
					foreach($arrProductData as $product)
					{
						if($product['source_cart_id']!=0)
						{	
							$this->Database->prepare("UPDATE tl_cart_items SET quantity_sold=" . $product['quantity_requested'] . " WHERE pid=? AND product_id=?")
								 	   	   ->execute($product['source_cart_id'], $product['product_id']);
							
						}
						
					}
					
						
					$objNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
											  ->limit(1)
											  ->execute($this->orderCompleteJumpTo);
	
					if ($objNextPage->numRows)
					{
						$postToURL = $this->generateFrontendUrl($objNextPage->fetchAssoc());
					}else{
						$postToURL = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
					}			
					
					$this->redirect($postToURL);
				}
				
				/*foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";
	
				$ch = curl_init($GLOBALS['TL_LANG']['MSC']['authNetUrlTest']); 
				###  Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
				### $ch = curl_init("https://secure.authorize.net/gateway/transact.dll"); 
				curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
				curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
				### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
				$resp = curl_exec($ch); //execute post and get results
				curl_close ($ch);
				
				$arrResponses = $this->handleResponse($resp);
				
				foreach(array_keys($arrResponses) as $key)
				{
					$arrReponseLabels[strtolower(standardize($key))] = $key;
				}
							
				$objTemplate->fields = $this->generateResponseString($arrResponses, $arrReponseLabels);
				
				$objTemplate->headline = $this->generateModuleHeadline($arrResponses['transaction-status']);
				
				$objTemplate->isConfirmation = true;
				
				$objTemplate->showPrintLink = true;*/
				
			}else{
				
				$arrBillingAddress = $this->getSelectedBillingAddress($this->intBillingAddressId);
			
				$objTemplate->x_version = $GLOBALS['TL_LANG']['MSC']['gatewayVersion'];
				$objTemplate->x_delim_data = $delimResponse;
				$objTemplate->x_delim_char = $delimChar;
				$objTemplate->x_relay_response = "false";	//Must be false for AIM processing.
				$objTemplate->x_login = $loginID;
				$objTemplate->x_tran_key = $transKey;
				$objTemplate->x_method = "CC";
				$objTemplate->x_type = $transType;
				$objTemplate->x_test_request = $status;
				
				$objTemplate->x_first_name = $arrBillingAddress['firstname'];
				$objTemplate->x_last_name = $arrBillingAddress['lastname'];
				$objTemplate->x_company = $arrBillingAddress['company'];
				$objTemplate->x_address = $arrBillingAddress['street'];
				$objTemplate->x_city = $arrBillingAddress['city'];
				$objTemplate->x_state = $arrBillingAddress['state'];
				$objTemplate->x_zip = $arrBillingAddress['postal'];
				$objTemplate->x_phone = $arrBillingAddress['phone'];
				$objTemplate->x_fax = $arrBillingAddress['fax'];
								
				$this->fltOrderTotal = $this->calculateOrderTotal();
				
				$objTemplate->taxTotals = $arrTaxInfo;
				
				//$objTemplate->x_email = $this->arrSession['FORM_DATA']['billing_information_email'];
				//$objTemplate->x_email_customer = "TRUE";
				$objTemplate->x_amount = $this->fltOrderTotal;
				$objTemplate->amountString = $this->generatePriceString(number_format($this->fltOrderTotal, 2), $this->strCurrency);
				$objTemplate->subtotal = $this->generatePriceString(number_format($this->fltOrderSubtotal, 2), $this->strCurrency);
				$objTemplate->shippingTotal = $this->generatePriceString(number_format($this->fltOrderShippingTotal, 2), $this->strCurrency);
				
				
				$objTemplate->x_card_num = $this->arrSession['x_card_num'];
				$objTemplate->x_exp_date = $this->arrSession['x_exp_date'];
				$objTemplate->x_cardholder_authentication_value = $this->arrSession['x_cardholder_authentication_value'];
				$objTemplate->order_comments = $this->generateWidget('tl_iso_orders','order_comments');
				$objTemplate->gift_message = $this->generateWidget('tl_iso_orders','gift_message');
				$objTemplate->gift_wrap = $this->generateWidget('tl_iso_orders','gift_wrap');
			}	
			
			//$this->Template->x_cust_id;
			
			//$objTemplate->formId = 'tl_aim_checkout';
			//$objTemplate->action = $action;
			
			$arrPaymentModules[] = array
			(
				'title'				=> $objPaymentModuleData->name,
				'paymentFields'		=> $objTemplate->parse()
			);
	  }
				
		return $arrPaymentModules;
	}
	
	protected function getSelectedBillingAddress($intAddressId = 0)
	{
		$objBillingAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")
											->limit(1)
											->execute($intAddressId);
	
		if($objBillingAddress->numRows < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['ERR']['specifyBillingAddress'];
		}
		
		$arrBillingAddress = $objBillingAddress->fetchAssoc();
	
		return $arrBillingAddress;
	}
	
	/**
	 * Returns an array containing all pertinent shipping information for enabled modules
	 * @param array
	 * @return array
	 */
	protected function getShippingServicesAndTiers($arrModuleIds)
	{
		foreach($arrModuleIds as $module)
		{
			//Load configuration data for the shipping method.
			$objShippingModuleData = $this->Database->prepare("SELECT s.name, sr.* FROM tl_shipping_modules s INNER JOIN tl_shipping_rates sr ON s.id=sr.pid WHERE s.id=?")
										  ->execute($module);
										  
			if($objShippingModuleData->numRows < 1)
			{
				continue;
			}
			
			//Gather all relevant data
			$arrShippingModuleData = $objShippingModuleData->fetchAllAssoc();
			
								
			foreach($arrShippingModuleData as $tier)
			{				
				$arrRates[] = array
				(
					(float)$tier['upper_limit'],
					(float)$tier['rate']
				);
			}
			
			$arrShippingModules[] = array
			(
				'rate_name'		=> $arrShippingModuleData[0]['name'] . ' ' . $arrShippingModuleData[0]['description'],
				'rates'			=> $arrRates
			);
		}
			
		return $arrShippingModules;
	}
	
	
	protected function calculateShippingCost($arrShippingModules)
	{
				
		foreach($arrShippingModules as $rate)
		{

			$i = 0;
			
			foreach($rate['rates'] as $rateTier)
			{
				if(!$blnRateIsSet)
				{	
					if((float)$this->fltOrderSubtotal < (float)$rateTier[0])
					{
						
						$fltShippingCost = (float)$rate['rates'][$i][1];
						
						$arrShippingMethods[] = array
						(
							'title' => $rate['rate_name'],
							'cost'	=> money_format('%n', $fltShippingCost)			
						);
						break;

					}
				}
			
				$i++;
			}
		
			
		}	
	
		
		//Set order total for payment
		
		
		$this->fltOrderShippingTotal = $fltShippingCost;
		return $arrShippingMethods;
		
	}
	
	protected function calculateOrderSubtotal($intCartId, $strUserId)
	{
		//Grab needed product data
		$arrAggregateSetData = $this->getCartProductsByCartId($intCartId, $strUserId);
			
		if(!sizeof($arrAggregateSetData))
		{
			$arrAggregateSetData = array();
		}
		
		$arrProductData = $this->getProductData($arrAggregateSetData, array('product_price'), 'product_price');
				
		return $this->getOrderTotal($arrProductData);
	
	}
	
	protected function calculateOrderTotal()
	{	
		return $this->fltOrderSubtotal + $this->fltOrderShippingTotal + $this->fltOrderTaxTotal;
	}
	
	/**
	 * For now this assumes all items are taxable.
	 */
	protected function calculateTax($arrProductData)
	{
		$this->import('FrontendUser','User');
				
		foreach($arrProductData as $row)
		{
			$arrTaxClasses[] = $row['tax_class'];	
		}
	
		//Get the tax rates for the given class.
		$arrTaxClassRecords = array_unique($arrTaxClasses);
				
		$strTaxRates = join(',', $arrTaxClassRecords);
		
		$objTaxRates = $this->Database->prepare("SELECT r.pid, r.country_id, r.region_id, r.postcode, r.rate, (SELECT name FROM tl_tax_class c WHERE c.id=r.pid) AS class_name FROM tl_tax_rate r WHERE r.pid IN(" . $strTaxRates . ")")
									  ->execute();
		
		if($objTaxRates->numRows < 1)
		{
			return 0.00;
		}
		
		$arrTaxRates = $objTaxRates->fetchAllAssoc();
		
		foreach($arrTaxRates as $rate)
		{
			//eventually this will also contain the formula or calc rule for the given tax rate.
			$arrRates[$rate['pid']] = array
			(
				'rate'			=> $rate['rate'],
				'country_id'	=> $rate['country_id'],
				'region_id'		=> $rate['region_id'],
				'postal_code'	=> $rate['postcode'],
				'class_name'	=> $rate['class_name']	//we need to output this to template for customers.
			);
		}
			
		//the calculation logic for tax rates will need to be something we can set in the backend eventually.  This is specific to Kolbo right now
		//as tax class 3 = luxury tax.
		foreach($arrProductData as $product)
		{
			
			$blnCalculate = false; 
			
			if($product['tax_class']!=0)
			{
				//only check what we need to.  There may be a better logic gate to express this but I haven't figured out what it is yet. ;)
				if(strlen($rate['postalcode']))
				{
					if($this->User->postal==$rate['postal_code'] && $this->User->state==$rate['region_id'] && $this->User->country==$rate['country_id'])
					{
						$blnCalculate = true;
					}
				}
				elseif(strlen($rate['region_id']))
				{
					if($this->User->state==$rate['region_id'] && $this->User->country==$rate['country_id'])
					{
						$blnCalculate = true;
					}
				}
				elseif(strlen($rate['country_id']))
				{
					if($this->User->country==$rate['country_id'])
					{
						$blnCalculate = true;
					}	
				}		
			
				if($blnCalculate)
				{
					//This needs to be database-driven.  We know what these tax values are right now and later it must not assume anything obviously.
					switch($product['tax_class'])
					{
						case '1':
								//if(strlen($rate['region_id']) > 0 && $this->User->state==$rate['region_id'])
								$fltSalesTax += (float)$product['product_price'] * $arrRates[$product['tax_class']]['rate'] / 100;
								
								//$arrTaxInfo['code'] = $
							break;
							
						case '3':	//Luxury tax.  5% of the difference over $175.00  this trumps standard sales tax.
							if((float)$product['product_price'] >= 175)
							{
								$fltTaxableAmount = (float)$product['product_price'] - 175;
								$fltSalesTax += $fltTaxableAmount * $arrRates[$product['tax_class']]['rate'] / 100;
							}else{
								//fallback if the price is below to standard sales tax.
								$fltTaxableAmount = (float)$product['product_price'] - 175;
								$fltSalesTax += $fltTaxableAmount * $arrRates[$product['tax_class']]['rate'] / 100;
							}
													
							break;
							
						case '2':	//because tax class 2 is exempt in Kolbo.
						default:
							break;			
					}
				}
			} //end if($product['tax_class'])
		}
		
		$this->fltOrderTaxTotal = number_format($fltSalesTax, 2);
		
		$arrTaxInfo[] = array
		(
			'class'			=> 'Sales Tax',
			'total'			=> $this->generatePriceString(number_format($fltSalesTax, 2), $this->strCurrency)
		);
		
		return $arrTaxInfo;
	}
	
	
	/**
	 */
	protected function calculateLuxuryTax($arrProductData)
	{
		foreach($arrProductData as $product)
		{
			
		}
		
		return $fltLuxuryTaxTotal;
	}
	
	/** return a widget object from another table
	*/
	protected function generateCommentWidget($strResourceTable, $strField)
	{
		$this->loadLanguageFile($strResourceTable);
		$this->loadDataContainer($strResourceTable);
		
		$arrData = &$GLOBALS['TL_DCA'][$strResourceTable]['fields'][$strField];
	
		$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
											
		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))// || !$arrData['eval']['isoEditable'])
		{
			return false;	
		}
	

		$objWidget = new $strClass($this->prepareForWidget($arrData, $strField, ''));

		$objWidget->storeValues = true;
		
		$this->arrSession['FORM_DATA'][$strField] = $objWidget->value;
			
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout')
		{
			//$objWidget->validate();
			$varValue = $objWidget->value;
		}
				
		$_SESSION['FORM_DATA'][$strField] = $varValue;
		
		$varSave = is_array($varValue) ? serialize($varValue) : $varValue;
		
		return $objWidget->generate();
	}
	
	/** return a widget object from another table
	*/
	protected function generateWidget($strResourceTable, $strField)
	{
		$this->loadLanguageFile($strResourceTable);
		$this->loadDataContainer($strResourceTable);
		
		$arrData = &$GLOBALS['TL_DCA'][$strResourceTable]['fields'][$strField];
	
		$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
											
		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))// || !$arrData['eval']['isoEditable'])
		{
			return false;	
		}
	

		$objWidget = new $strClass($this->prepareForWidget($arrData, $strField, ''));

		$objWidget->storeValues = true;
		
		$this->arrSession['FORM_DATA'][$strField] = $objWidget->value;
			
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout')
		{
			//$objWidget->validate();
			$varValue = $objWidget->value;
		}
				
		$_SESSION['FORM_DATA'][$strField] = $varValue;
		
		$varSave = is_array($varValue) ? serialize($varValue) : $varValue;
		
		return $objWidget->generate();
	}

	
	
	
	protected function generateAddressWidget($intUserId, $strCurrentStep, $field)
	{
		global $objPage;
				
		$GLOBALS['TL_LANGUAGE'] = $objPage->language;
		
		$this->loadLanguageFile('tl_address_book');
		$this->loadDataContainer('tl_address_book');
		
		$objUserAddressData = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=?")
											 ->execute($intUserId);
							
		if($objUserAddressData->numRows < 1)
		{
			return false;
		}
		
		$objTemplate = new FrontendTemplate('iso_checkout_billing_information');
		
		$arrUserAddressEntries = $objUserAddressData->fetchAllAssoc();
		
		//form the options
		foreach($arrUserAddressEntries as $address)
		{
			$arrOptions = array();
			
			foreach($address as $k=>$v)
			{
				switch($k)
				{
					case 'tstamp':
					case 'sorting':
					case 'pid':
					case 'phone':
					case 'email':
						continue;
						break;
					case 'isDefaultShipping':
						if($v==1)
						{
							$intDefaultShippingId = $address['id'];
						}
						break;
					case 'isDefaultBilling':
						if($v==1)
						{
							if(!isset($this->intBillingAddressId))
							{
								$this->intBillingAddressId = $address['id'];	//set for use in checkout
							}
							
							$intDefaultBillingId = $address['id'];
							
						}
						
						break;
					default:
						$arrAddresses[$address['id']][$k] = $v;						
						break; 
				}
								
			}
			
			foreach($arrAddresses as $address)
			{
				$intAddressId = $address['id'];
				
				unset($address['id']);
				
				if(strlen($address['street_2'])<1)
				{
					unset($address['street_2']);
				}else{
					$address['street'] .= '<br />' . $address['street_2'];
				}
				
				if(strlen($address['street_3'])<1)
				{
					unset($address['street_3']);
				}else{
					$address['street'] .= '<br />' . $address['street_3'];
				}
				
				
				$arrOptions[] = array
				(
					'value'		=> $intAddressId,
					'label'		=> $address['firstname'] . ' ' . $address['lastname'] . '<br />' .$address['street'] . '<br />' . $address['city'] . ', ' . $address['state'] . '<br />' . $GLOBALS['TL_LANG']['CNT'][$address['country']] . '<br /><br />'
				);
			}
		}
		
		$intDefaultValue = 0;
		
		switch($strCurrentStep)
		{
			case 'billing_information':
				$intDefaultValue = $intDefaultBillingId;
				break;
			
			case 'shipping_information':
				$intDefaultValue = $intDefaultShippingId;
				break;
		
			default:
				break;
		}
		
	
		
		$strClass = $GLOBALS['TL_FFL']['radio'];
											
		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))
		{
			return false;	
		}
	
		$arrData['inputType'] = 'radio';
		
		//echo $intDefaultValue;
		
		$objWidget = new $strClass($this->prepareForWidget($arrData, $field, $intDefaultValue));
		
		$objWidget->options = $arrOptions;
		
		$objWidget->storeValues = true;
		
		$this->arrSession['FORM_DATA'][$field] = $objWidget->value;
			
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == $strCurrentStep)
		{
			//$objWidget->validate();
			$varValue = $objWidget->value;
		}
				
		$_SESSION['FORM_DATA'][$field] = $varValue;
		//echo $_SESSION['FORM_DATA'][$field];
		$varSave = is_array($varValue) ? serialize($varValue) : $varValue;
		
		$objTemplate->fields = $objWidget->parse();
		//$objTemplate->formId = $strCurrentStep;
		//$objTemplate->formSubmit = $strCurrentStep;
		//$objTemplate->method = 'post';
		//$objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
		//$objTemplate->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		
		return $objTemplate->parse();

	}
	
	/**
	 * Generate the current step widgets.
	 * strResourceTable is used either to load a DCA or else to gather settings related to a given DCA.
	 *
	 */
	protected function getCurrentStepWidgets($strCurrentStep, $intStoreId, $strResourceTable, $blnLoadDataContainer = true, $arrFieldData = null)
	{
		
		
		
		global $objPage;
		$this->import('FrontendUser', 'User');

		$GLOBALS['TL_LANGUAGE'] = $objPage->language;
			
		$objTemplate = new FrontendTemplate('form');
						
		if($blnLoadDataContainer)
		{
				
			$this->loadLanguageFile($strResourceTable);
			$this->loadDataContainer($strResourceTable);
			
			$arrStepFields = $GLOBALS['TL_DCA'][$strResourceTable]['fields'];
			
		}else{
			//Get static temp dca info to proceed with widget generation
			$arrStepFields = array(); //Which will be another DCA defined somewhere.
		}
		
		foreach($arrStepFields as $field=>$data)
		{
						
			//Create a widget...
			$arrData = &$GLOBALS['TL_DCA'][$strResourceTable]['fields'][$field];
			
			
			$strGroup = $arrData['eval']['feGroup'];
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
											
			// Continue if the class is not defined
			if (!$this->classFileExists($strClass) || !$arrData['eval']['isoEditable'] || !sizeof($arrData['eval']['isoCheckoutGroups']))
			{
				continue;
			}
			
			if(sizeof($arrData['eval']['isoCheckoutGroups']) && !in_array($strCurrentStep, $arrData['eval']['isoCheckoutGroups']))
			{
				continue;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, $strCurrentStep . '_' . $field, $this->User->$field));
			
			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
			
			$this->arrSession['FORM_DATA'][$strCurrentStep . '_' . $field] = $objWidget->value;
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout')//$strResourceTable . '_' . $this->id)
			{
				$objWidget->validate();
				$varValue = $objWidget->value;
				//$strUsername = strlen($this->Input->post('username')) ? $this->Input->post('username') : $this->User->username;

				// Check whether the password matches the username
				if ($objWidget instanceof FormPassword && $varValue == sha1($strUsername))
				{
					$objWidget->addError($GLOBALS['TL_LANG']['ERR']['passwordName']);
				}

				// Convert date formats into timestamps
				if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Make sure that unique fields are unique
				if ($arrData['eval']['unique'])
				{
					$objUnique = $this->Database->prepare("SELECT * FROM " . $strResourceTable . " WHERE " . $field . "=? AND id!=?")
												->limit(1)
												->execute($varValue, $this->User->id);

					if ($objUnique->numRows)
					{
						$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field)));
					}
				}

				// Do not submit if there are errors
				if ($objWidget->hasErrors())
				{
					$doNotSubmit = true;
				}

				// Store current value
				elseif ($objWidget->submitInput())
				{
					// Save callback
					if (is_array($arrData['save_callback']))
					{
						foreach ($arrData['save_callback'] as $callback)
						{
							$this->import($callback[0]);
							$varValue = $this->$callback[0]->$callback[1]($varValue, $this->User);
						}
					}

					// Set new value
					$this->User->$field = $varValue;

					// Save field
					$this->Database->prepare("UPDATE " . $strResourceTable . " SET " . $field . "=? WHERE id=?")
								   ->execute($varSave, $this->User->id);

					// HOOK: set new password callback
					if ($objWidget instanceof FormPassword && array_key_exists('setNewPassword', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['setNewPassword']))
					{
						foreach ($GLOBALS['TL_HOOKS']['setNewPassword'] as $callback)
						{
							$this->import($callback[0]);
							$this->$callback[0]->$callback[1]($this->User, $varValue);
						}
					}
				}
			}

			if ($objWidget instanceof uploadable)
			{
				$hasUpload = true;
			}

			$temp = $objWidget->parse();
				
			$fields .= $temp;
			$arrFields[$field] .= $temp;
			$objTemplate->fields .= $temp;

			$this->Session->setData($this->arrSession);
		}
		
		// Redirect or reload if there was no error
		if ($this->Input->post('FORM_SUBMIT') == $strResourceTable . '_' . $this->id && !$doNotSubmit)
		{
			//$this->jumpToOrReload($this->jumpTo);
		}

		/*$objTemplate->loginDetails = $GLOBALS['TL_LANG'][$strResourceTable]['loginDetails'];
		$objTemplate->addressDetails = $GLOBALS['TL_LANG'][$strResourceTable]['addressDetails'];
		$objTemplate->contactDetails = $GLOBALS['TL_LANG'][$strResourceTable]['contactDetails'];
		$objTemplate->personalData = $GLOBALS['TL_LANG'][$strResourceTable]['personalData'];
		*/

		// Add groups
		/*foreach ($arrFields as $k=>$v)
		{
			$objTemplate->$k = $v;
		}*/
		
	
		//$objTemplate->formId = $strResourceTable . '_' . $this->id;
		//$objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
		//$objTemplate->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		//$objTemplate->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		//$objTemplate->rowLast = 'row_' . count($this->editable) . ((($i % 2) == 0) ? ' odd' : ' even');	
		
		return $objTemplate->parse();	
	}
	
	protected function getSelections($strResourceTable)
	{
		//$objSelections = $this->Database->prepare("SELECT options FROM " . $strResourceTable )
	
	}
	
	protected function generateRequiredFieldData($strCurrentStep)
	{
		return $GLOBALS['TEMP_DCA'][$strCurrentStep]['fields'];
	}
	
	//*** AUTHORIZE.NET Processing code - move to authorize class module and call that as the standard approach for handling and rendering out data?
	
	private function addAlert($alertText)
	{
		return "<span style=\"color:#ff0000;\">" . $alertText . "</span>";
	}
	
	private function generateModuleHeadline($strOrderStatus)
	{
		switch($strOrderStatus)
		{
			case "Approved":
				return "Your Order Is Complete!";
				break;
				
			case "Declined":
				return "Your payment method has been declined.";
				break;
			
			case "Error":
				return "There was an error with your payment method.";
				break;
			default:
				return;			
		}
	}
	
	private function generateResponseString($arrResponses, $arrResponseLabels)
	{
		$responseString .= '<tr><td align="right" colspan="2">&nbsp;</td></tr>';
			
			$showReason = false;
						
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
						
						$value = $this->addAlert($v) . "<br /><a href=\"" . $this->session['infoPage'] . "\"><strong>Click here to review and correct your order</strong></a>";
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