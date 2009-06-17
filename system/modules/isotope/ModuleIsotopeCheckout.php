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
	
//	protected $arrStoreSettings = array();
	
//	protected $arrSession = array();
	
//	protected $strOrderStatus;
	
//	protected $fltOrderTotal = 0.00;
	
//	protected $fltOrderSubtotal = 0.00;
	
//	protected $fltOrderShippingTotal = 0.00;
	
//	protected $fltOrderTaxTotal = 0.00;
	
	protected $intBillingAddressId = 0;
	
	protected $intShippingAddressId = 0;	
	
	protected $intShippingRateId = 0;
	
	protected $doNotSubmit = false;
	
	protected $strCurrentStep;
	
	protected $strFormId = 'iso_mod_checkout';

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
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Set template
		if (strlen($this->iso_checkout_layout))
		{
			$this->strTemplate = $this->iso_checkout_layout;
		}
		
		
		if(($this->iso_checkout_method == 'login' && !FE_USER_LOGGED_IN) || ($this->iso_checkout_method == 'both' && !FE_USER_LOGGED_IN && !$_SESSION['isotope']['isGuest']))
		{
			$this->blnShowLoginOptions = true;
		}
		
//		$this->arrSession = $this->Session->getData();
		
/* 		$this->arrJumpToValues = $this->getStoreJumpToValues($this->store_id);	//Deafult keys are "product_reader", "shopping_cart", and "checkout" */

		$this->strCurrentStep = $this->Input->get('step');
		
		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{	
		//$GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS'] = array('login','shipping_information','billing_information','shipping_method','billing_method','order_review');

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
//		$this->Template = new FrontendTemplate($this->strTemplate);
		//}
		
		$action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);

		$this->Template->action = $action;		
		$this->Template->formId = $this->strFormId;
		$this->Template->formSubmit = $this->strFormId;
		$this->Template->rowLast = 'row_' . (count($this->editable) + 1) . ((($i % 2) == 0) ? ' odd' : ' even');
		
		if($this->Cart->items)
		{
			$this->Template->previousLabel = specialchars($GLOBALS['TL_LANG']['MSC']['previousStep']);
			$this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['nextStep']);
			$this->Template->showPrevious = true;
			$this->Template->showNext = true;
		}

		$intBillingAddressId = ($this->Input->post('billing_address') ? $this->Input->post('billing_address') : $_SESSION['FORM_DATA']['billing_address']);
		
		if($intBillingAddressId > 0)
		{
			//echo $intBillingAddressId;
			$this->intBillingAddressId = $intBillingAddressId;
		}
		
		//FOR LATER WHEN AN OPTION LIST IS SELECTED.
		/*$intShippingMethod = ($this->Input->post('shipping_methods') ? $this->Input->post('shipping_methods') : $_SESSION['FORM_DATA']['shipping_methods']);
		
		if()
		{
			$this->intShippingRateId = $intShippingMethod;
		}
		*/
		
		$intShippingAddressId = ($this->Input->post('shipping_address') ? $this->Input->post('shipping_address') : $_SESSION['FORM_DATA']['shipping_address']);

		if((integer)$intShippingAddressId > 0)
		{
			//echo $intBillingAddressId;
			$this->intShippingAddressId = $intShippingAddressId;
		}
		

		if(!$this->Cart->items)
		{
			$arrSteps[] = array
			(
				'editEnabled' => false,
				'headline'	  => 'Error',
				'prompt'	  => '',
				'fields'	  => '<div class="noItems">' . $GLOBALS['TL_LANG']['MSC']['noItemsInCart'] . '</div>'
			);
		}
		else
		{
			// Send user to the first step
			if (!strlen($this->strCurrentStep) || !in_array($this->strCurrentStep, $GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS']))
			{
				$this->redirectToNextStep();
			}
			
			//Specific actions for the given step
			//Each step gets resources from some table somewhere.  It may be the address book, it may be the payment method's payment fields
			//
			switch($this->strCurrentStep)
			{
				case 'login':
					if (!$this->blnShowLoginOptions)
						$this->redirectToNextStep();
						
					$arrSteps[] = array
					(
						'editEnabled' 	=> false,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
						'fields' 		=> $this->getLoginInterface(),
						'useFieldset' 	=> false
					);										
					break;
				
				case 'billing_information':
					if($this->blnShowLoginOptions)
						break;
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
						'fields' 		=> $this->generateAddressWidget('billing_address'),
						'useFieldset' 	=> false
					);
					
					$this->Template->showPrevious = false;
										
					break;
				
				case 'shipping_information':
					if($this->blnShowLoginOptions)
						break;

					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
						'fields' 		=> $this->generateAddressWidget('shipping_address'),
						'useFieldset' 	=> true
					);
					break;
				
				case 'shipping_method':
					if($this->blnShowLoginOptions)
						break;
						
					// FIXME: hack so user must select a valid payment method
					unset($_SESSION['FORM_DATA']['payment']);
					
					//blnLoadDataContainer is set to "false" because we do not gather our widget from those fields. Instead we statically define
					//the fields for now that are used.
					//$arrFieldData = $this->generateRequiredFieldData($step);
					//$this->getCurrentStepWidgets($step, 'tl_shipping_methods', false, $arrFieldData);
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
						'useFieldset' 	=> true,
						'fields' 		=> $this->getShippingModulesInterface(deserialize($this->iso_shipping_modules))
						//'fields' 	=> $this->getCurrentStepWidgets($step, 'tl_shipping_methods', false, $arrFieldData)
					);
					break;
				
				
				case 'payment_method':
//						continue;
					if($this->blnShowLoginOptions)
						break;
						
					//blnLoadDataContainer is set to "false" because we do not gather our widget from those fields. Instead we statically define
					//the fields for now that are used.
					//$arrFieldData = $this->generateRequiredFieldData($step);
					//$this->getCurrentStepWidgets($step, 'tl_payment_methods', false, $arrFieldData);
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> true,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
						'useFieldset' 	=> true,
						'fields' 		=> $this->getPaymentModulesInterface(deserialize($this->iso_payment_modules))
					);
					
					break;
				
				case 'order_review':
//					if($this->blnShowLoginOptions)
//						break;

					//$session = $this->arrSession->getData('ISO_CHECKOUT');
					
					// Write order to database but don't drop the cart
					$this->writeOrder(false);	
					
					$arrSteps[] = array
					(
						'editEnabled' 	=> false,
						'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
						'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
						'useFieldset' 	=> true,
						'fields'		=> $this->getOrderReviewInterface(),
					);
					
					$this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
					break;
					
				case 'order_complete':
				
					// Hide buttons
					$this->Template->showNext = false;
					$this->Template->showPrevious = false;					
					
					if ($this->Cart->Payment->processPayment())
					{
						$this->writeOrder(true);
					
						$this->jumpToOrReload($this->orderCompleteJumpTo);
					}
					else
					{
						$arrSteps[] = array
						(
							'editEnabled' 	=> false,
							'headline' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE'][$this->strCurrentStep],
							'prompt' 		=> $GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT'][$this->strCurrentStep],
							'useFieldset' 	=> true,
							'fields'		=> 'Zahlung wird bearbeitet...',
						);
					}
					
					break;
					
				case 'order_failed';
					die('Bestellung fehlgeschlagen');
					break;
			}
		}
		
		
		// User pressed "back" button
		if (strlen($this->Input->post('previousStep')))
		{
			$this->redirectToPreviousStep();
		}
		
		// Valid input data, redirect to next step
		elseif ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit)
		{
			$this->redirectToNextStep();
		}
				
		$this->Template->checkoutSteps = $arrSteps;
	}
	
	
	protected function redirectToNextStep()
	{
		if (!in_array($this->strCurrentStep, $GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS']))
		{
			$this->redirect($this->addToUrl('step='.array_shift($GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS'])));
		}
		else
		{
			$arrSteps = array_values($GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS']);
			$strKey = array_search($this->strCurrentStep, $arrSteps);
			$this->redirect($this->addToUrl('step='.$arrSteps[($strKey+1)]));
		}
	}
	
	protected function redirectToPreviousStep()
	{
		if (!in_array($this->strCurrentStep, $GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS']))
		{
			$this->redirect($this->addToUrl('step='.array_shift($GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS'])));
		}
		else
		{
			$arrSteps = array_values($GLOBALS['ISO_CONFIG']['CHECKOUT_STEPS']);
			$strKey = array_search($this->strCurrentStep, $arrSteps);
			$this->redirect($this->addToUrl('step='.$arrSteps[($strKey-1)]));
		}
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
		$objTemplate->createNewAddressFields = $this->getCurrentStepWidgets($step, 'tl_address_book');
		
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
		$arrData = $this->Input->post('shipping');
		
		$arrData = is_array($arrData) ? $arrData : $_SESSION['FORM_DATA']['shipping'];
		
		if (!strlen($arrData['module']))
		{
			$this->doNotSubmit = true;
		}
		else
		{
			$_SESSION['FORM_DATA']['shipping'] = $arrData;
		}
		
		$arrShippingMethods = $this->getShippingModules($arrModuleIds, $arrData);
				
		if(!count($arrShippingMethods))
		{
			$this->Template->showNext = false;
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noShippingModules'] . '</i>';
		}

		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'shipping_method');
		$objTemplate->shippingMethods = $arrShippingMethods;
		return $objTemplate->parse();	
	}
	
	
	protected function getPaymentModulesInterface($arrModuleIds)
	{
		$arrData = $this->Input->post('payment');
		
		$arrData = is_array($arrData) ? $arrData : $_SESSION['FORM_DATA']['payment'];
		
		if (!strlen($arrData['module']))
		{
			$this->doNotSubmit = true;
		}
		else
		{
			$_SESSION['FORM_DATA']['payment'] = $arrData;
		}
		
		$arrModules = $this->getPaymentModules($arrModuleIds, $arrData);
		
		if(!count($arrModules))
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}
		
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'payment_method');

		//Get rendered module html by payment option.
		$objTemplate->paymentMethods = $arrModules;
	
		return $objTemplate->parse();
	}
	
	
	/**
	 * @todo Guest cannot be found in tl_user, emailCustomer() will fail
	 */
	protected function writeOrder($blnCheckout=false)
	{
		$arrBillingAddress = $this->getSelectedAddress('billing');
		$strBillingAddress = $this->getAddressString($arrBillingAddress);
		$arrShippingAddress = $this->getSelectedAddress('shipping');
		$strShippingAddress = $this->getAddressString($arrShippingAddress);		
		
		$arrSet = array
		(
			'billing_address'		=> $strBillingAddress,
			'shipping_address'		=> $strShippingAddress,
			'pid'					=> (FE_USER_LOGGED_IN ? $this->User->id : 0),
			'tstamp'				=> time(),
			'store_id'				=> $this->Store->id,
			'cart_id'				=> $this->Cart->id,
			'source_cart_id'		=> $this->Cart->id,
			'subTotal'				=> $this->Isotope->formatPriceWithCurrency($this->Cart->subTotal),		// + ($this->Input->post('gift_wrap') ? 10 : 0),		// FIXME
			'taxTotal'	 			=> $this->Isotope->formatPriceWithCurrency($this->Cart->taxTotalWithShipping),
			'shippingTotal'			=> $this->Isotope->formatPriceWithCurrency($this->Cart->Shipping->price),
			'grandTotal'			=> $this->Isotope->formatPriceWithCurrency($this->Cart->grandTotal),
			'shipping_method'		=> $this->Cart->Shipping->label,
			'payment_method'		=> $this->Cart->Payment->label,
			'status'				=> ($blnCheckout ? $this->Cart->Payment->new_order_status : ''),
			'language'				=> $GLOBALS['TL_LANGUAGE'],
		);
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->execute($this->Cart->id);
		
		if (!$objOrder->numRows)
		{
			$objOrder = $this->Database->prepare("INSERT INTO tl_iso_orders %s")
									   ->set($arrSet)
									   ->execute();
									   
			$orderId = $objOrder->insertId;
		}
		else
		{
			$this->Database->prepare("UPDATE tl_iso_orders %s WHERE id=?")
						   ->set($arrSet)
						   ->execute($objOrder->id);
						   
			$orderId = $objOrder->id;
		}
		
		$this->Database->prepare("UPDATE tl_iso_orders SET order_id=? WHERE id=?")->execute(($this->Store->orderPrefix . $orderId), $orderId);
		

		if ($blnCheckout)
		{
			$arrData = array
			(
				'orderId'					=> ($this->Store->orderPrefix . $orderId),
				'items'						=> $this->Cart->items,
				'products'					=> $this->Cart->products,
				'subTotal'					=> $this->Isotope->formatPriceWithCurrency($this->Cart->subTotal),
				'taxTotal'					=> $this->Isotope->formatPriceWithCurrency($this->Cart->taxTotal),
				'taxTotalWithShipping'		=> $this->Isotope->formatPriceWithCurrency($this->Cart->taxTotalWithShipping),
				'shippingPrice'				=> $this->Isotope->formatPriceWithCurrency($this->Cart->Shipping->price),
				'grandTotal'				=> $this->Isotope->formatPriceWithCurrency($this->Cart->grandTotal),
				'cart_text'					=> $this->Cart->getProductsAsString(),
				'cart_html'					=> $this->Cart->getProductsAsHtml(),
				'billing_address'			=> $strBillingAddress,
				'billing_address_html'		=> nl2br($strBillingAddress),
				'shipping_address'			=> $strShippingAddress,
				'shipping_address_html'		=> nl2br($strShippingAddress),
				'shipping_method'			=> $this->Cart->Shipping->label,
				'shipping_note'				=> $this->Cart->Shipping->note,
				'shipping_note_text'		=> strip_tags($this->Cart->Shipping->note),
				'payment_method'			=> $this->Cart->Payment->label,
				'payment_note'				=> $this->Cart->Payment->note,
				'payment_note_text'			=> strip_tags($this->Cart->Payment->note),
			);
			
			foreach( $arrBillingAddress as $k => $v )
			{
				$arrData['billing_'.$k] = $v;
			}
		
			$this->log('New order ID ' . $orderId . ' has been placed', 'ModuleIsotopeCheckout writeOrder()', TL_ACCESS);
			$this->Isotope->sendMail($this->iso_mail_admin, $GLOBALS['TL_ADMIN_EMAIL'], $GLOBALS['TL_LANGUAGE'], $arrData);
			$this->Isotope->sendMail($this->iso_mail_customer, $arrBillingAddress['email'], $GLOBALS['TL_LANGUAGE'], $arrData);

			$this->Cart->delete();
			unset($_SESSION['FORM_DATA']);
			unset($_SESSION['isotope']);
		}
		
		return ($this->Store->orderPrefix . $orderId);
	}
	
	
	/**
	 * send an email confirmation to customers
	 *
	 * @param string
	 * @param string
	 * @return null
	 */
/*
	protected function emailCustomer($arrAddress)
	{
		$objEmail = new Email();
		
		$strData = sprintf($GLOBALS['TL_LANG']['MSC']['message_new_order_customer_thank_you'], ($arrAddress['firstname'] . ' ' . $arrAddress['lastname']), $this->Cart->getProductsAsString(), $GLOBALS['TL_ADMIN_EMAIL']);
				
		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['subject_new_order_customer_thank_you'], $GLOBALS['TL_LANG']['MSC']['store_title']);
		$objEmail->text = $strData;
		$objEmail->sendTo($arrAddress['email']);
		
		return true;
	}
*/
	
	
	/**
	 * Send an admin notification e-mail
	 * @param integer
	 * @param array
	 */
/*
	protected function sendAdminNotification($intOrderId, $arrData)
	{
		$objEmail = new Email();

		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['subject_new_order_admin_notify'], $this->Environment->host); 

		$strData = "\n\n";

		// Add order details
		foreach ($arrData as $row)
		{
			$strData .= $row['label'] . $row['value'] . "\n";
		}

		$objEmail->text = sprintf($GLOBALS['TL_LANG']['MSC']['message_new_order_admin_notify'], $intOrderId, $strData . "\n") . "\n";
		$objEmail->sendTo($GLOBALS['TL_ADMIN_EMAIL']);

		$this->log('New order ID ' . $intOrderId . ' has been placed', 'ModuleIsotopeCheckout sendAdminNotification()', TL_ACCESS);
		
		return true;
	}
*/
	
	
	protected function getShippingModules($arrModuleIds, $arrData)
	{
		if (!is_array($arrModuleIds) || !count($arrModuleIds))
			return array();
			
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT * FROM tl_shipping_modules WHERE id IN (" . implode(',', $arrModuleIds) . ") AND enabled='1'");
		
		while( $objModules->next() )
		{
			$strClass = $GLOBALS['ISO_SHIP'][$objModules->type];
			
			if (!strlen($strClass) || !$this->classFileExists($strClass))
			{
				continue;	
			}
			
			$objModule = new $strClass($objModules->row());
			
			if (!$objModule->available)
				continue;
			
			$arrModules[] = sprintf('<input id="ctrl_shipping_module_%s" type="radio" name="shipping[module]" value="%s"%s /> <label for="ctrl_shipping_module_%s">%s: %s</label>',
									 $objModule->id,
									 $objModule->id,
									 ($arrData['module'] == $objModule->id ? ' checked="checked"' : ''),
									 $objModule->id,
 									 $objModule->label,
 									 $this->Isotope->formatPriceWithCurrency($objModule->price));
		}
				
		return $arrModules;
	}
	
	protected function getPaymentModules($arrModuleIds, $arrData)
	{
		if (!is_array($arrModuleIds) || !count($arrModuleIds))
			return array();
			
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT * FROM tl_payment_modules WHERE id IN (" . implode(',', $arrModuleIds) . ")");
		
		while( $objModules->next() )
		{
			$strClass = $GLOBALS['ISO_PAY'][$objModules->type];
			
			if (!strlen($strClass) || !$this->classFileExists($strClass))
			{
				continue;	
			}
			
			$objModule = new $strClass($objModules->row());
			
			if (!$objModule->available)
				continue;
			
			$arrModules[] = sprintf('<input id="ctrl_payment_module_%s" type="radio" name="payment[module]" value="%s"%s /> <label for="ctrl_payment_module_%s">%s: %s</label>',
									 $objModule->id,
									 $objModule->id,
									 ($arrData['module'] == $objModule->id ? ' checked="checked"' : ''),
									 $objModule->id,
 									 $objModule->label,
 									 $this->Isotope->formatPriceWithCurrency($objModule->price));
			
/*
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
			$arrProductData = $this->Isotope->getProductData($this->Cart->getProducts(), array('product_price','tax_class'), 'product_price');

			$arrTaxInfo = $this->calculateTax($arrProductData, $this->strCurrentStep);	
			
			
			$arrFields = array('cc_num','cc_exp','cc_cvv','cc_type');

			$arrPaymentFields = $this->getPaymentFields('tl_iso_orders',$arrFields);
			
			//var_dump($arrPaymentFields);
			$objTemplate->fields = $arrPaymentFields;
			
						
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit)
			{
				
				$arrBillingAddress = $this->getSelectedAddress($this->intBillingAddressId, 'billing_information');
				$arrShippingAddress = $this->getSelectedAddress($this->intShippingAddressId, 'shipping_information');
				
				$strBillingAddress = $this->getAddressString($arrBillingAddress);
				$strShippingAddress = $this->getAddressString($arrShippingAddress);
				
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
					"x_card_num"			=> $this->Input->post('cc_num'),
					"x_card_type"			=> $this->Input->post('cc_type'),
					"x_exp_date"			=> $this->Input->post('cc_exp'),
					"x_card_code"			=> $this->Input->post('cc_cvv'),
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
				
							
				$arrPaymentInfo = array('order_tax' => $this->fltOrderTaxTotal, 'cc_num' => $this->Input->post('cc_num'), 'cc_type' => $this->Input->post('cc_type'), 'cc_exp' => $this->Input->post('cc_exp'), 'cc_cvv' => $this->Input->post('cc_cvv'), 'order_comments' => $this->Input->post('order_comments'), 'billing_firstname'=>$arrBillingAddress['firstname'], 'billing_lastname'=>$arrBillingAddress['lastname'], 'billing_street'=>$arrBillingAddress['street'],'billing_street_2'=>$arrBillingAddress['street_2'],'billing_street_3'=>$arrBillingAddress['street_3'],'billing_city'=>$arrBillingAddress['city'],'billing_state'=>$arrBillingAddress['state'],'billing_postal'=>$arrBillingAddress['postal'],'billing_country'=>$arrBillingAddress['country'], 'shipping_firstname'=>$arrShippingAddress['firstname'], 'shipping_lastname'=>$arrShippingAddress['lastname'], 'shipping_street'=>$arrShippingAddress['street'],'shipping_street_2'=>$arrShippingAddress['street_2'],'shipping_street_3'=>$arrShippingAddress['street_3'],'shipping_city'=>$arrShippingAddress['city'],'shipping_state'=>$arrShippingAddress['state'],'shipping_postal'=>$arrShippingAddress['postal'],'shipping_country'=>$arrShippingAddress['country']); //billing_address_id'=>$this->intBillingAddressId, 'shipping_address_id'=>$this->intShippingAddressId, 'gift_message' => $this->Input->post('gift_message'), 'gift_wrap' => $this->Input->post('gift_wrap'), 'billing_address'=>$strBillingAddress,'shipping_address'=>$strShippingAddress);
												
				if($this->writeOrder($arrPaymentInfo) && !$this->doNotSubmit)
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
				
				// foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";
				// 
				// $ch = curl_init($GLOBALS['TL_LANG']['MSC']['authNetUrlTest']); 
				// ###  Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
				// ### $ch = curl_init("https://secure.authorize.net/gateway/transact.dll"); 
				// curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
				// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
				// curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
				// ### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
				// $resp = curl_exec($ch); //execute post and get results
				// curl_close ($ch);
				// 
				// $arrResponses = $this->handleResponse($resp);
				// 
				// foreach(array_keys($arrResponses) as $key)
				// {
				// 	$arrReponseLabels[strtolower(standardize($key))] = $key;
				// }
				// 			
				// $objTemplate->fields = $this->generateResponseString($arrResponses, $arrReponseLabels);
				// 
				// $objTemplate->headline = $this->generateModuleHeadline($arrResponses['transaction-status']);
				// 
				// $objTemplate->isConfirmation = true;
				// 
				// $objTemplate->showPrintLink = true;
				
			}
			else
			{
						
				$arrBillingAddress = $this->getSelectedAddress($this->intBillingAddressId);
				
				$arrFields = array('cc_num','cc_exp','cc_cvv','cc_type');
				
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
				$objTemplate->x_amount = $this->fltOrderTotal + ($this->Input->post('gift_wrap')==1 ? 10 : 0);
				$objTemplate->amountString = $this->generatePrice($this->fltOrderTotal);
				$objTemplate->subtotal = $this->generatePrice($this->Cart->subTotal);
				$objTemplate->shippingTotal = $this->generatePrice($this->fltOrderShippingTotal);
				
				
				$objTemplate->x_card_num = $this->arrSession['cc_num'];
				$objTemplate->x_exp_date = $this->arrSession['cc_exp'];
				$objTemplate->x_card_code = $this->arrSession['cc_cvv'];
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
*/
		}
				
		return $arrModules;
	}
	
	
	protected function getOrderReviewInterface()
	{
		$objTemplate = new FrontendTemplate('iso_checkout_order_review');
		
		$strForm = $this->Cart->hasPayment ? $this->Cart->Payment->checkoutForm() : '';
		
		if ($strForm !== false)
		{
			$this->Template->showNext = false;
			$this->Template->checkoutForm = $strForm;
		}
					
		
		$arrProductData = $this->Isotope->getProductData($this->Cart->getProducts(), array('product_alias','product_name','product_price', 'product_media'), 'product_name');
		
		$objTemplate->products = $this->formatProductData($arrProductData);
		
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$objTemplate->taxLabel = sprintf($GLOBALS['TL_LANG']['MSC']['taxLabel'], 'Sales');
		$objTemplate->shippingLabel = $GLOBALS['TL_LANG']['MSC']['shippingLabel'];
		
		$objTemplate->subTotalPrice = $this->generatePrice($this->Cart->subTotal);
		$objTemplate->shippingTotal = $this->generatePrice($this->Cart->Shipping->price);
		$objTemplate->taxTotal = $this->generatePrice($this->Cart->taxTotalWithShipping);
		$objTemplate->grandTotalPrice = $this->generatePrice($this->Cart->grandTotal, 'stpl_total_price');
		
		$objTemplate->billingAddress = $this->getAddressString($this->getSelectedAddress('billing'));
		$objTemplate->shippingAddress = $this->getAddressString($this->getSelectedAddress('shipping'));
		
		$objTemplate->shippingMethod = $this->Cart->Shipping->label;
		$objTemplate->paymentMethod = $this->Cart->Payment->label;
		
		return $objTemplate->parse();
	}
	
	
/*
	protected function getPaymentFields($strResourceTable, $arrFields)
	{
		
		foreach($arrFields as $field)
		{
			$arrFieldCollection[] = $this->generateWidget($strResourceTable, $field, true);
		}
		
		return $arrFieldCollection;
	}
*/
	
	protected function getSelectedAddress($strStep = 'billing')
	{
		$intAddressId = $_SESSION['FORM_DATA'][$strStep.'_address'];
		
		// Take billing address
		if ($intAddressId == -1)
		{
			$intAddressId = $_SESSION['FORM_DATA']['billing_address'];
			$strStep = 'billing';
		}
		
		if ($intAddressId == 0)
		{
			$arrAddress = array
			(
				'company'		=> $_SESSION['FORM_DATA'][$strStep . '_information_company'],
				'firstname'		=> $_SESSION['FORM_DATA'][$strStep . '_information_firstname'],
				'lastname'		=> $_SESSION['FORM_DATA'][$strStep . '_information_lastname'],
				'street'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street'],
				'street_2'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_2'],
				'street_3'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_3'],
				'city'			=> $_SESSION['FORM_DATA'][$strStep . '_information_city'],
				'state'			=> $_SESSION['FORM_DATA'][$strStep . '_information_state'],
				'postal'		=> $_SESSION['FORM_DATA'][$strStep . '_information_postal'],
				'country'		=> $_SESSION['FORM_DATA'][$strStep . '_information_country'],
			);
			
			if ($strStep == 'billing')
			{
				$arrAddress['email'] = (strlen($_SESSION['FORM_DATA'][$strStep . '_information_email']) ? $_SESSION['FORM_DATA'][$strStep . '_information_email'] : $this->User->email);
				$arrAddress['phone'] = (strlen($_SESSION['FORM_DATA'][$strStep . '_information_phone']) ? $_SESSION['FORM_DATA'][$strStep . '_information_phone'] : $this->User->phone);
			}
		}
		else
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")
												->limit(1)
												->execute($intAddressId);
		
			if($objAddress->numRows < 1)
			{
				return $GLOBALS['TL_LANG']['MSC']['ERR']['specifyBillingAddress'];
			}
			
			$arrAddress = $objAddress->fetchAssoc();
			$arrAddress['email'] = $this->User->email;
			$arrAddress['phone'] = $this->User->phone;
		}
				
		return $arrAddress;
	}
	
	
	/**
	 * Generate an address string for order (overview and order table)
	 * 
	 * @todo should use and sort by the selected fields in store configuration
	 * @access protected
	 * @param array $arrAddress
	 * @return string
	 */
	protected function getAddressString($arrAddress)
	{
		$arrCountries = $this->getCountries();
		
		$strAddress  = (strlen($arrAddress['company']) > 0 ? $arrAddress['company'] . "\n" : '');
		$strAddress .= $arrAddress['firstname'] . ' ' . $arrAddress['lastname'] . "\n";
		$strAddress .= $arrAddress['street'] . "\n";
		$strAddress .= (strlen($arrAddress['street_2']) > 0 ? $arrAddress['street_2'] . "\n" : '');
		$strAddress .= (strlen($arrAddress['street_3']) > 0 ? $arrAddress['street_3'] . "\n" : '');
		$strAddress .= $arrAddress['postal'] . ' ' . $arrAddress['city'] . "\n";
		$strAddress .= (strlen($arrAddress['state']) > 0 ? $arrAddress['state'] . "\n" : '');
		$strAddress .= $arrCountries[$arrAddress['country']] . "\n\n";
		$strAddress .= (strlen($arrAddress['email']) > 0 ? $arrAddress['email'] . "\n" : '');
		$strAddress .= (strlen($arrAddress['phone']) > 0 ? $arrAddress['phone'] . "\n" : '');
	
	/*
		foreach( $this->Store->address_fields as $strField )
		{
			if (!isset($GLOBALS['TL_DCA'][$strResourceTable]['fields'][$strField]))
				continue;
				
			$arrStepFields[$strField] = $GLOBALS['TL_DCA'][$strResourceTable]['fields'][$strField];
		}
	*/
		return $strAddress;
	}
	
	/**
	 * Returns an array containing all pertinent shipping information for enabled modules
	 * @param array
	 * @return array
	 */
/*
	protected function getShippingServicesAndTiers($arrModuleIds)
	{
		if (!is_array($arrModuleIds) || !count($arrModuleIds))
			return array();

		foreach($arrModuleIds as $module)
		{
			//Load configuration data for the shipping method.
			$objShippingModuleData = $this->Database->prepare("SELECT s.name, sr.* FROM tl_shipping_modules s INNER JOIN tl_shipping_rates sr ON s.id=sr.pid WHERE s.id=?")
										  ->execute($module);
										  
			if(!$objShippingModuleData->numRows)
			{
				continue;
			}
			
			//Gather all relevant data
			$arrShippingModuleData = $objShippingModuleData->fetchAllAssoc();
			$arrRates = array();
								
			foreach($arrShippingModuleData as $tier)
			{				
				$arrRates[] = array
				(
					(float)$tier['upper_limit'],
					(float)$tier['rate'],
					$tier['id']
				);
			}
			
			$arrShippingModules[] = array
			(
				'id'			=> $arrShippingModuleData['id'],
				'rate_name'		=> $arrShippingModuleData[0]['name'] . ' ' . $arrShippingModuleData[0]['description'],
				'rates'			=> $arrRates
			);
		}
			
		return $arrModules;
	}
	
*/
/*
	
	protected function calculateShippingCost($arrShippingModules)
	{
		if (!is_array($arrShippingModules) || !count($arrShippingModules))
			return array();
			
		foreach($arrShippingModules as $rate)
		{
			$i = 0;
		
			foreach($rate['rates'] as $rateTier)
			{
				if(!$blnRateIsSet)
				{	
					if((float)$this->Cart->subTotal < (float)$rate['rates'][$i][0])
					{
						$fltShippingCost = (float)$rate['rates'][$i][1];
						$this->intShippingRateId = $rate['rates'][$i][2];	//Only assign here until a choice can be made!!!!!
						
						$arrShippingMethods[] = array
						(
							'shipping_rate_id'		=> $rate['rates'][$i][2],
							'title' 				=> $rate['rate_name'],
							'cost'					=> $this->Isotope->formatPriceWithCurrency($fltShippingCost),
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
*/
	
/*
	protected function calculateOrderSubtotal($intCartId, $strUserId)
	{
		//Grab needed product data
		$arrAggregateSetData = $this->Cart->getProducts();
			
		if(!sizeof($arrAggregateSetData))
		{
			$arrAggregateSetData = array();
		}
		
		$arrProductData = $this->Isotope->getProductData($arrAggregateSetData, array('product_price'), 'product_price');
				
		return $this->getOrderTotal($arrProductData);
	
	}
*/
	
	/**
	 * @todo: where is $fltGiftWrap coming from?
	 */
/*
	protected function calculateOrderTotal()
	{	
		return $this->Cart->subTotal + $fltGiftWrap + $this->fltOrderShippingTotal + $this->fltOrderTaxTotal;
	}
	
*/
	/**
	 * For now this assumes all items are taxable.
	 */
/*
	protected function calculateTax($arrProductData)
	{
		// FIXME
		return 0;
		$this->import('FrontendUser','User');
				
		foreach($arrProductData as $row)
		{
			$arrTaxClasses[] = $row['tax_class'];	
		}
	
		//Get the tax rates for the given class.
		$arrTaxClassRecords = array_unique($arrTaxClasses);
		
		if(sizeof($arrTaxClassRecords))
		{		
			$strTaxRates = join(',', $arrTaxClassRecords);
		}
		
		if(strlen(trim($strTaxRates)) < 1)
		{
			return array();
		}
		
		
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
		
		$arrBillingAddress = $this->getSelectedAddress($this->intBillingAddressId, 'billing_information'); //Tax calculated based on billing address.
		$arrShippingAddress = $this->getSelectedAddress($this->intShippingAddressId, 'shipping_information');
		
		$arrAddresses[] = $arrBillingAddress;
		$arrAddresses[] = $arrShippingAddress;
		
		//the calculation logic for tax rates will need to be something we can set in the backend eventually.  This is specific to Kolbo right now
		//as tax class 3 = luxury tax.
		foreach($arrProductData as $product)
		{
			$blnAlreadyCalculatedTax = false;
			$blnCalculate = false;
			
			foreach($arrAddresses as $address)
			{
				if($product['tax_class']!=0)
				{
					//only check what we need to.  There may be a better logic gate to express this but I haven't figured out what it is yet. ;)
					if(strlen($rate['postalcode']))
					{
						if($address['postal']==$rate['postal_code'] && $address['state']==$rate['region_id'] && $address['country']==$rate['country_id'])
						{
							$blnCalculate = true;
						}
					}
					elseif(strlen($rate['region_id']) && strlen($rate['country_id']))
					{
						if($address['state']==$rate['region_id'] && $address['country']==$rate['country_id'])
						{
							
							$blnCalculate = true;
						}
					}
//					elseif(strlen($rate['country_id']))
//					{
//						if($address['country']==$rate['country_id'])
//						{
//							$blnCalculate = true;
//						}	
//					}		
					
					if($blnCalculate && !$blnAlreadyCalculatedTax)
					{
						//This needs to be database-driven.  We know what these tax values are right now and later it must not assume anything obviously.
						switch($product['tax_class'])
						{
							case '1':
									//if(strlen($rate['region_id']) > 0 && $this->User->state==$rate['region_id'])
									$fltSalesTax += (float)$product['product_price'] * $arrRates[$product['tax_class']]['rate'] / 100;
									
									//$arrTaxInfo['code'] = $
								break;
								
							case '2':	//Luxury tax.  5% of the difference over $175.00  this trumps standard sales tax.
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
								
							case '3':	//because tax class 2 is exempt in Kolbo.
							default:
								break;			
						}
						
						$blnAlreadyCalculatedTax = true;
					}
				} //end if($product['tax_class'])
			}//end foreach($arrAddresses)
		}
		
		$this->fltOrderTaxTotal = number_format($fltSalesTax, 2);
	
		$arrTaxInfo[] = array
		(
			'class'			=> 'Sales Tax',
			'total'			=> $this->generatePrice($fltSalesTax)
		);
		
		return $arrTaxInfo;
	}
*/
	
	
/*
	protected function calculateLuxuryTax($arrProductData)
	{
		foreach($arrProductData as $product)
		{
			
		}
		
		return $fltLuxuryTaxTotal;
	}
*/
	
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
		
		$_SESSION['FORM_DATA'][$strField] = $objWidget->value;
			
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
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
	protected function generateWidget($strResourceTable, $strField, $blnUseTable = false)
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
		
		$_SESSION['FORM_DATA'][$strField] = $objWidget->value;
			
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
		{
			$objWidget->validate();
			$varValue = $objWidget->value;

			// Convert date formats into timestamps
			if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
			{
				$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
				$varValue = $objDate->tstamp;
			}

			if ($objWidget->hasErrors())
			{
				$this->doNotSubmit = true;
			}

			// Store current value
			elseif ($objWidget->submitInput())
			{
//				$arrUser[$field] = $varValue;
			}
		}
				
		$_SESSION['FORM_DATA'][$strField] = $varValue;
		
		$varSave = is_array($varValue) ? serialize($varValue) : $varValue;
		
		if($blnUseTable)
		{
			return '<tr class="' .  $objWidget->rowClass . '">
	    <td class="col_0 col_first">' . $objWidget->generateLabel() . ($objWidget->mandatory ? '<span class="mandatory">*</span>' : '') . '</td>
	    <td class="col_1 col_last">' . $objWidget->generateWithError() . '</td>
	  </tr>';
		}
		else
		{
			return $objWidget->generateWithError();
		}
	}

	
	
	
	protected function generateAddressWidget($field)
	{
		$this->loadLanguageFile('tl_address_book');
		$this->loadDataContainer('tl_address_book');
		
		$objTemplate = new FrontendTemplate('iso_checkout_billing_information');
		
		$arrOptions = array();
		
		if (FE_USER_LOGGED_IN)
		{
			$objUserAddressData = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=?")->execute($this->User->id);
			
			if ($objUserAddressData->numRows)
			{
				$arrUserAddressEntries = $objUserAddressData->fetchAllAssoc();
				
				//form the options
				foreach($arrUserAddressEntries as $address)
				{
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
						}
						else
						{
							$address['street'] .= '<br />' . $address['street_2'];
						}
						
						if(strlen($address['street_3'])<1)
						{
							unset($address['street_3']);
						}
						else
						{
							$address['street'] .= '<br />' . $address['street_3'];
						}
						
						
						$arrOptions[] = array
						(
							'value'		=> $intAddressId,
							'label'		=> $address['firstname'] . ' ' . $address['lastname'] . '<br />' .$address['street'] . '<br />' . $address['city'] . ', ' . $address['state'] . '<br />' . $GLOBALS['TL_LANG']['CNT'][$address['country']] . '<br /><br />'
						);
					}
				}
			}
		}
		
		/*
		send registry items to registry owner.
		*/
		
		switch($this->strCurrentStep)
		{
			case 'billing_information':
				$intDefaultValue = ($intDefaultBillingId ? $intDefaultBillingId : 0);
				
				if (FE_USER_LOGGED_IN)
				{
					$arrOptions[] = array
					(
						'value'	=> 0,
						'label' => &$GLOBALS['TL_LANG']['createNewAddressLabel'],
					);
				}
				break;
			
			case 'shipping_information':
				$intDefaultValue = ($intDefaultShippingId ? $intDefaultShippingId : -1);
				
				$arrOptions[] = array
				(
					'value'	=> -1,
					'label' => &$GLOBALS['TL_LANG']['useBillingAddress'],
				);
				
				$arrOptions[] = array
				(
					'value'	=> 0,
					'label' => &$GLOBALS['TL_LANG']['differentShippingAddress'],
				);

				break;
		
			default:
				break;
		}
	
		if (count($arrOptions))
		{
			$strClass = $GLOBALS['TL_FFL']['radio'];
			
			$objWidget = new $strClass($this->prepareForWidget($arrData, $field, (strlen($_SESSION['FORM_DATA'][$field]) ? $_SESSION['FORM_DATA'][$field] : $intDefaultValue)));
			$objWidget->options = $arrOptions;
			$objWidget->onclick = "Isotope.toggleAddressFields(this, '" . $this->strCurrentStep . "_new');";
					
			$objWidget->storeValues = true;
			
//			$_SESSION['FORM_DATA'][$field] = $objWidget->value;
				
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
			{
				$objWidget->validate();
				$varValue = $objWidget->value;
				
				$_SESSION['FORM_DATA'][$field] = $varValue;
			}
			
			$objTemplate->fields = $objWidget->parse();
		}
		
		$objTemplate->fields .= '<div id="' . $this->strCurrentStep . '_new"' . (((!FE_USER_LOGGED_IN && $this->strCurrentStep == 'billing_information') || $objWidget->value == 0) ? '' : ' style="display:none">');
		$objTemplate->fields .= '<span><h3>' . $GLOBALS['TL_LANG']['createNewAddressLabel'] . '</h3>' . $this->getCurrentStepWidgets('tl_address_book', $field) . '</span>';
		$objTemplate->fields .= '</div>';
		
		//$objTemplate->formId = $this->strCurrentStep;
		//$objTemplate->formSubmit = $this->strCurrentStep;
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
	protected function getCurrentStepWidgets($strResourceTable, $strAddressField, $blnLoadDataContainer = true, $arrFieldData = null)
	{	
		$objTemplate = new FrontendTemplate('fields_insert');
						
		if($blnLoadDataContainer)
		{
			$this->loadLanguageFile($strResourceTable);
			$this->loadDataContainer($strResourceTable);
			
			$arrStepFields = array();
			
			foreach( $this->Store->address_fields as $strField )
			{
				if (!isset($GLOBALS['TL_DCA'][$strResourceTable]['fields'][$strField]))
					continue;
					
				$arrStepFields[$strField] = $GLOBALS['TL_DCA'][$strResourceTable]['fields'][$strField];
			}
		}
		else
		{
			//Get static temp dca info to proceed with widget generation
			$arrStepFields = array(); //Which will be another DCA defined somewhere.
		}

		foreach($arrStepFields as $field => $arrData)
		{
			$strGroup = $arrData['eval']['feGroup'];
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
			
			// Continue if the class is not defined
			if (!$this->classFileExists($strClass) || !$arrData['eval']['isoEditable'] || !sizeof($arrData['eval']['isoCheckoutGroups']))
			{
				continue;
			}
			
			if(sizeof($arrData['eval']['isoCheckoutGroups']) && !in_array($this->strCurrentStep, $arrData['eval']['isoCheckoutGroups']))
			{
				continue;
			}
			
			// Special field "country"
			if ($field == 'country')
			{
				$arrCountries = array_combine(array_values($this->Store->countries), array_keys($this->Store->countries));
				
				$arrData['options'] = array_intersect_key($arrData['options'], $arrCountries);
				$arrData['default'] = $this->Store->country;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, $this->strCurrentStep . '_' . $field, (strlen($_SESSION['FORM_DATA'][$this->strCurrentStep . '_' . $field]) ? $_SESSION['FORM_DATA'][$this->strCurrentStep . '_' . $field] : (strlen($this->User->$field) ? $this->User->$field : $arrData['default']))));
			
			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
			
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && $this->Input->post($strAddressField) == 0)//$strResourceTable . '_' . $this->id)
			{
				$objWidget->validate();
				
				$varValue = $objWidget->value;

				// Convert date formats into timestamps
				if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Do not submit if there are errors
				if ($objWidget->hasErrors())
				{
					$this->doNotSubmit = true;
				}

				// Store current value
				elseif ($objWidget->submitInput())
				{
					$_SESSION['FORM_DATA'][$this->strCurrentStep . '_' . $field] = $varValue;
/*
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
//					$this->User->$field = $varValue;

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
*/
				}
			}

/*
			if ($objWidget instanceof uploadable)
			{
				$hasUpload = true;
			}
*/

			$temp = $objWidget->parse();
				
			$fields .= $temp;
			$arrFields[$field] .= $temp;
			$objTemplate->fields .= $temp;

//			$this->Session->setData($this->arrSession);
		}
		
		// Redirect or reload if there was no error
		if ($this->Input->post('FORM_SUBMIT') == $strResourceTable . '_' . $this->id && !$this->doNotSubmit)
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
	
/*
	protected function getSelections($strResourceTable)
	{
		//$objSelections = $this->Database->prepare("SELECT options FROM " . $strResourceTable )
	
	}
	
*/
/*
	protected function generateRequiredFieldData()
	{
		return $GLOBALS['TEMP_DCA'][$this->strCurrentStep]['fields'];
	}
*/
	
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