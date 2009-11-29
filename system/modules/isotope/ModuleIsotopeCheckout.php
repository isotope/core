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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeCheckout extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_checkout';

	protected $strStepTemplateBaseName = 'iso_checkout_';
	
	public $doNotSubmit = false;
	
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

		$this->strCurrentStep = $this->Input->get('step');
		
		return parent::generate();
	}

	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		// Return error message if cart is empty
		if (!$this->Cart->items)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
			return;
		}


		// Redirect to login page if not logged in
		if($this->iso_checkout_method == 'member' && !FE_USER_LOGGED_IN)
		{
			$objPage = $this->Database->prepare("SELECT id,alias FROM tl_page WHERE id=?")->limit(1)->execute($this->iso_login_jumpTo);
			
			if (!$objPage->numRows)
			{
				$this->Template = new FrontendTemplate('mod_message');
				$this->Template->type = 'error';
				$this->Template->message = $GLOBALS['TL_LANG']['ERR']['isoLoginRequired'];
				return;
			}
			
			$this->redirect($this->generateFrontendUrl($objPage->row()));
		}
		elseif($this->iso_checkout_method == 'guest' && FE_USER_LOGGED_IN)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'error';
			$this->Template->message = 'User checkout not allowed';
			return;
		}

		
		// Default template settings. Must be set at beginning so they can be overwritten later (eg. trough callback)
		$this->Template->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->formId = $this->strFormId;
		$this->Template->formSubmit = $this->strFormId;
		$this->Template->previousLabel = specialchars($GLOBALS['TL_LANG']['MSC']['previousStep']);
		$this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['nextStep']);
		$this->Template->nextClass = 'next';
		$this->Template->showPrevious = true;
		$this->Template->showNext = true;
		$this->Template->showForm = true;
		
		
		// Remove shipping step if no items are shipped
		if (!$this->Cart->requiresShipping)
		{
			unset($GLOBALS['ISO_CHECKOUT_STEPS']['shipping']);
		}
		
		
		if ($this->strCurrentStep == 'failed')
		{
			$this->Database->prepare("UPDATE tl_iso_orders SET status='failed' WHERE cart_id=?")->execute($this->Cart->id);
			$this->Template->mtype = 'error';
			$this->Template->message = strlen($this->Input->get('reason')) ? $this->Input->get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
			$this->strCurrentStep = 'review';
		}
		
		
		// Run trough all steps until we find the current one or one reports failure
		foreach( $GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks )
		{
			$strBuffer = '';
			foreach( $arrCallbacks as $callback )
			{
				if ($callback[0] == 'ModuleIsotopeCheckout')
				{
					$strBuffer .= $this->{$callback[1]}();
				}
				else
				{
					$this->import($callback[0]);
					$strBuffer .= $this->{$callback[0]}->{$callback[1]}($this);
				}
				
				if ($this->doNotSubmit && $step != $this->strCurrentStep)
				{
					$this->redirect($this->addToUrl('step=' . $step));
				}
			}
			
			if ($step == $this->strCurrentStep)
				break;
		}
		
		
		if ($this->strCurrentStep == 'complete')
		{
			$strBuffer = $this->Cart->Payment->processPayment();
				
			if ($strBuffer === true)
			{
				$strUniqueId = $this->writeOrder(true);
				$this->redirect($this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->orderCompleteJumpTo)->fetchAssoc(), '/uid/'.$strUniqueId));
			}
			else
			{
				$this->writeOrder(false);
				$this->Template->showNext = false;
				$this->Template->showPrevious = false;
			}
		}
		
		$this->Template->fields = $strBuffer;
		
		if (!strlen($this->strCurrentStep))
			$this->strCurrentStep = $step;
		
		
		// Show checkout steps
		$arrStepKeys = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);
		$blnStepPassed = true;
		$arrSteps = array();
		foreach( $arrStepKeys as $i => $step )
		{
			if ($this->strCurrentStep == $step)
				$blnStepPassed = false;
				
			$arrSteps[] = array
			(
				'class'	=> $step . ($this->strCurrentStep == $step ? ' active' : '') . ($blnStepPassed ? ' passed' : '') . ($i == 0 ? ' first' : ''),
				'label'	=> (strlen($GLOBALS['TL_LANG']['ISO']['checkout_'.$step]) ? $GLOBALS['TL_LANG']['ISO']['checkout_'.$step] : $step),
				'href'	=> ($blnStepPassed ? $this->addToUrl('step='.$step) : ''),
				'title'	=> 'Go back to step "'.$step.'"',
			);
		}
		
		$arrSteps[count($arrSteps)-1]['class'] .= ' last';
		$this->Template->steps = $arrSteps;
		
		
		// Hide back buttons it this is the first step
		if (array_search($this->strCurrentStep, $arrStepKeys) === 0)
		{
			$this->Template->showPrevious = false;
		}
		
		// Show "confirm order" button if this is the last step
		elseif (array_search($this->strCurrentStep, $arrStepKeys) === (count($arrStepKeys)-1))
		{
			$this->Template->action = $this->addToUrl('step=complete');
			$this->Template->nextClass = 'confirm';
			$this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
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
	}
	
	
	/**
	 * Redirect visitor to the next step in ISO_CHECKOUT_STEPS
	 */
	protected function redirectToNextStep()
	{
		$arrSteps = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);
		if (!in_array($this->strCurrentStep, $arrSteps))
		{
			$this->redirect($this->addToUrl('step='.array_shift($arrSteps)));
		}
		else
		{
			$strKey = array_search($this->strCurrentStep, $arrSteps);
			$this->redirect($this->addToUrl('step='.$arrSteps[($strKey+1)]));
		}
	}
	
	
	/**
	 * Redirect visotor to the previous step in ISO_CHECKOUT_STEPS
	 */
	protected function redirectToPreviousStep()
	{
		$arrSteps = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);
		if (!in_array($this->strCurrentStep, $arrSteps))
		{
			$this->redirect($this->addToUrl('step='.array_shift($arrSteps)));
		}
		else
		{
			$strKey = array_search($this->strCurrentStep, $arrSteps);
			$this->redirect($this->addToUrl('step='.$arrSteps[($strKey-1)]));
		}
	}
	
	
		
	
	

	
	protected function getBillingAddressInterface()
	{
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'billing_address');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['billing_address'];
		$objTemplate->message = (FE_USER_LOGGED_IN ? $GLOBALS['TL_LANG']['ISO']['billing_address_message'] : $GLOBALS['TL_LANG']['ISO']['billing_address_guest_message']);
		$objTemplate->fields = $this->generateAddressWidget('billing_address');
		
		return $objTemplate->parse();
	}
	
	protected function getShippingAddressInterface()
	{
		if (!$this->Cart->requiresShipping)
			return '';
			
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'shipping_address');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['shipping_address'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['shipping_address_message'];
		$objTemplate->fields =  $this->generateAddressWidget('shipping_address');
		
		return $objTemplate->parse();
	}
	
	protected function getShippingModulesInterface()
	{
		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_shipping_modules);
		
		if (is_array($arrModuleIds) && count($arrModuleIds))
		{
			$arrData = $this->Input->post('shipping');
			$objModules = $this->Database->execute("SELECT * FROM tl_shipping_modules WHERE id IN (" . implode(',', $arrModuleIds) . ") AND enabled='1'");
			
			while( $objModules->next() )
			{
				$strClass = $GLOBALS['ISO_SHIP'][$objModules->type];
				
				if (!strlen($strClass) || !$this->classFileExists($strClass))
					continue;
				
				$objModule = new $strClass($objModules->row());
				
				if (!$objModule->available)
					continue;
					
				if (is_array($arrData) && $arrData['module'] == $objModule->id)
	 			{
	 				$_SESSION['CHECKOUT_DATA']['shipping'] = $arrData;
	 			}
	 			
	 			if (is_array($_SESSION['CHECKOUT_DATA']['shipping']) && $_SESSION['CHECKOUT_DATA']['shipping']['module'] == $objModule->id)
	 			{
	 				$this->Cart->Shipping = $objModule;
	 			}
				
				$arrModules[] = sprintf('<input id="ctrl_shipping_module_%s" type="radio" name="shipping[module]" value="%s"%s /> <label for="ctrl_shipping_module_%s">%s: %s</label>%s%s',
										 $objModule->id,
										 $objModule->id,
										 ($this->Cart->Shipping->id == $objModule->id ? ' checked="checked"' : ''),
										 $objModule->id,
	 									 $objModule->label,
	 									 $this->Isotope->formatPriceWithCurrency($objModule->price), 
	 									 ($objModule->note ? '<div class="clearBoth"></div><br /><div class="shippingNote"><strong>Note:</strong><br />' . $objModule->note . '</div>' : ''),
	 									 ($objModule->getShippingOptions($objModule->id) ? '<div class="clearBoth"></div><br /><div class="shippingOptions"><strong>Options:</strong><br />' . $objModule->getShippingOptions($objModule->id) . '</div>' : ''));
	 									 
	 			$objLastModule = $objModule;
			}
		}
				
		if(!count($arrModules))
		{
			$this->doNotSubmit = true;
			$this->Template->showNext = false;
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noShippingModules'] . '</i>';
		}
		elseif (!$this->Cart->hasShipping && !strlen($_SESSION['CHECKOUT_DATA']['shipping']['module']) && count($arrModules) == 1)
		{
			$this->Cart->Shipping = $objLastModule;
			$_SESSION['CHECKOUT_DATA']['shipping']['module'] = $this->Cart->Shipping->id;
		}
		elseif (!$this->Cart->hasShipping)
		{
			$this->doNotSubmit = true;
		}

		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'shipping_method');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['shipping_method'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['shipping_method_message'];
		$objTemplate->shippingMethods = $arrModules;
		
		return $objTemplate->parse();	
	}
	
	
	protected function getPaymentModulesInterface()
	{
		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_payment_modules);
		
		if (is_array($arrModuleIds) && count($arrModuleIds))
		{
			$arrData = $this->Input->post('payment');
			$objModules = $this->Database->execute("SELECT * FROM tl_payment_modules WHERE id IN (" . implode(',', $arrModuleIds) . ") AND enabled='1'");
			
			while( $objModules->next() )
			{
				$strClass = $GLOBALS['ISO_PAY'][$objModules->type];
				
				if (!strlen($strClass) || !$this->classFileExists($strClass))
					continue;
				
				$objModule = new $strClass($objModules->row());
							
				if (!$objModule->available)
					continue;
					
				if (is_array($arrData) && $arrData['module'] == $objModule->id)
	 			{
	 				$_SESSION['CHECKOUT_DATA']['payment'] = $arrData;
	 			}
	 			
	 			if (is_array($_SESSION['CHECKOUT_DATA']['payment']) && $_SESSION['CHECKOUT_DATA']['payment']['module'] == $objModule->id)
	 			{
	 				$this->Cart->Payment = $objModule;
	 			}
							
				$arrModules[] = sprintf('<input id="ctrl_payment_module_%s" type="radio" name="payment[module]" value="%s"%s /> <label for="ctrl_payment_module_%s">%s</label>',
										 $objModule->id,
										 $objModule->id,
										 ($this->Cart->Payment->id == $objModule->id ? ' checked="checked"' : ''),
										 $objModule->id,
	 									 $objModule->label);
	 									 
	 			$objLastModule = $objModule;
			}
		}
		
		if(!count($arrModules))
		{
			$this->doNotSubmit = true;
			$this->Template->showNext = false;
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}
		elseif (!$this->Cart->hasPayment && !strlen($_SESSION['CHECKOUT_DATA']['payment']['module']) && count($arrModules) == 1)
		{
			$this->Cart->Payment = $objLastModule;
			$_SESSION['CHECKOUT_DATA']['payment']['module'] = $this->Cart->Payment->id;
		}
		elseif (!$this->Cart->hasPayment)
		{
			$this->doNotSubmit = true;
		}
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'payment_method');

		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['payment_method'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['payment_method_message'];
		$objTemplate->paymentMethods = $arrModules;
	
		return $objTemplate->parse();
	}
	
	protected function getOrderConditionsInterface()
	{
		if (!$this->iso_order_conditions)
			return '';
					
		$strBuffer = $this->getArticle($this->iso_order_conditions, false, true);
		
		$objConditions = new FormCheckBox(array('id'=>'iso_conditions', 'name'=>'iso_conditions', 'options'=>array(array('value'=>'1', 'label'=>$GLOBALS['TL_LANG']['MSC']['order_conditions'])), 'tableless'=>true));
		
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
		{
			$objConditions->validate();
			
			if (!strlen($objConditions->value))
			{
				$objConditions->addError($GLOBALS['TL_LANG']['ERR']['order_conditions']);
			}
			
			if ($objConditions->hasErrors())
			{
				$this->doNotSubmit = true;
			}
		}
		
		return $strBuffer . '<div class="order_conditions">' . $objConditions->parse() . '</div>';
	}
	
	protected function getOrderReviewInterface($blnWriteOrder=true)
	{
		if ($blnWriteOrder)
		{
			$this->writeOrder(false);
		}
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'order_review');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['order_review'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['order_review_message'];
		
		$strForm = $this->Cart->hasPayment ? $this->Cart->Payment->checkoutForm() : '';

		if ($strForm !== false)
		{
			$this->Template->showForm = false;
		}
					
		
		$arrSurcharges = array();
		foreach( $this->Cart->getSurcharges() as $arrSurcharge )
		{
			$arrSurcharges[] = array
			(
				'label'			=> $arrSurcharge['label'],
				'price'			=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['price']),
				'total_price'	=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['total_price']),
				'tax_id'		=> $arrSurcharge['tax_id'],
			);
		}
		
		
		$arrProductData = array();
		$arrProducts = $this->Cart->getProducts();
		
		foreach( $arrProducts as $objProduct )
		{
			$arrProductData[] = array_merge($objProduct->getAttributes(), array
			(
				'id'				=> $objProduct->id,
				'image'				=> $objProduct->images[0],
				'link'				=> $objProduct->href_reader,
				'price'				=> $objProduct->formatted_price,
				'total_price'		=> $objProduct->formatted_total_price,
				'quantity'			=> $objProduct->quantity_requested,
				'tax_id'			=> $objProduct->tax_id,
			));
		}
		
		
		$objTemplate->products = $arrProductData;
		$objTemplate->surcharges = $arrSurcharges;
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$objTemplate->subTotalPrice = $this->generatePrice($this->Cart->subTotal);
		$objTemplate->grandTotalPrice = $this->generatePrice($this->Cart->grandTotal, 'stpl_total_price');

		$objTemplate->billingAddress = $this->Isotope->generateAddressString($this->Cart->billingAddress);
		$objTemplate->shippingAddress = $this->Cart->shippingAddress['id'] == -1 ? '' : $this->Isotope->generateAddressString($this->Cart->shippingAddress);
		$objTemplate->shippingMethod = ($this->Cart->hasShipping ? $this->Cart->Shipping->checkoutReview() : '');
		$objTemplate->paymentMethod = $this->Cart->Payment->checkoutReview();
		$objTemplate->checkoutForm = $strForm;

		return $objTemplate->parse();
	}

	
	
	
	
	
	
	
	/**
	 * @todo Guest cannot be found in tl_user, emailCustomer() will fail
	 */
	protected function writeOrder($blnCheckout=false)
	{
		$strUniqueId = uniqid($this->Isotope->Store->orderPrefix, true);
	
		$arrSet = array
		(
			'pid'					=> (FE_USER_LOGGED_IN ? $this->User->id : 0),
			'tstamp'				=> time(),
			'date'					=> time(),
			'uniqid'				=> $strUniqueId,
			'store_id'				=> $this->Isotope->Store->id,
			'cart_id'				=> $this->Cart->id,
			//'source_cart_id'		=> $this->Cart->id,
			
			'subTotal'				=> $this->Cart->subTotal,		// + ($this->Input->post('gift_wrap') ? 10 : 0),		
			'taxTotal'	 			=> $this->Cart->taxTotal,
			'shippingTotal'			=> $this->Cart->shippingTotal,
			'grandTotal'			=> $this->Cart->grandTotal,
			'surcharges'			=> $this->Cart->getSurcharges(),
			
			'shipping_method'		=> ($this->Cart->hasShipping ? $this->Cart->Shipping->checkoutReview() : ''),
			'payment_method'		=> $this->Cart->Payment->checkoutReview(),
			'status'				=> ($blnCheckout ? $this->Cart->Payment->new_order_status : ''),
			'language'				=> $GLOBALS['TL_LANGUAGE'],
			'billing_address'		=> serialize($this->Cart->billingAddress),
			'shipping_address'		=> serialize($this->Cart->shippingAddress),
			'currency'				=> $this->Isotope->Store->currency
		);
		
		//FIXME?  Sort of strange way to have to handle credit card data...
		if($_SESSION['FORM_DATA']['cc_num'] && $_SESSION['FORM_DATA']['cc_exp'])
		{			
			$arrSet['cc_num'] 	= $_SESSION['FORM_DATA']['cc_num'];
			$arrSet['cc_exp'] 	= $_SESSION['FORM_DATA']['cc_exp'];
			$arrSet['cc_type'] = isset($_SESSION['FORM_DATA']['cc_type']) ? $_SESSION['FORM_DATA']['cc_type'] : "";
			$arrSet['cc_cvv'] = isset($_SESSION['FORM_DATA']['cc_cvv']) ? $_SESSION['FORM_DATA']['cc_cvv'] : "";
		}
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Cart->id);
		
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
		
		$this->Database->prepare("UPDATE tl_iso_orders SET order_id=? WHERE id=?")->execute(($this->Isotope->Store->orderPrefix . $orderId), $orderId);
		
							
		$fltShippingTotal = (float)$this->Cart->Shipping->price + (float)$this->Cart->Shipping->optionsPrice;
		
		if ($blnCheckout)
		{
			$strBillingAddress = $this->Isotope->generateAddressString($this->Cart->billingAddress);
			$strShippingAddress = $this->Cart->shippingAddress['id'] == -1 ? '' : $this->Isotope->generateAddressString($this->Cart->shippingAddress);

			$arrData = array
			(
				'order_id'					=> ($this->Isotope->Store->orderPrefix . $orderId),
				'items'						=> $this->Cart->items,
				'products'					=> $this->Cart->products,
				'subTotal'					=> $this->Isotope->formatPriceWithCurrency($this->Cart->subTotal),
				'taxTotal'					=> $this->Isotope->formatPriceWithCurrency($this->Cart->taxTotal),
				'taxTotalWithShipping'		=> $this->Isotope->formatPriceWithCurrency($this->Cart->taxTotalWithShipping),
				'shippingPrice'				=> $this->Isotope->formatPriceWithCurrency($fltShippingTotal),
				'grandTotal'				=> $this->Isotope->formatPriceWithCurrency($this->Cart->grandTotal),
				'cart_text'					=> $this->Cart->getProducts('iso_products_text'),
				'cart_html'					=> $this->Cart->getProducts('iso_products_html'),
				'billing_address'			=> $strBillingAddress,
				'billing_address_text'		=> str_replace('<br />', "\n", $strBillingAddress),
				'shipping_address'			=> $strShippingAddress,
				'shipping_address_text'		=> str_replace('<br />', "\n", $strShippingAddress),
				'shipping_method'			=> $this->Cart->Shipping->label,
				'shipping_note'				=> $this->Cart->Shipping->note,
				'shipping_note_text'		=> strip_tags($this->Cart->Shipping->note),
				'payment_method'			=> $this->Cart->Payment->label,
				'payment_note'				=> $this->Cart->Payment->note,
				'payment_note_text'			=> strip_tags($this->Cart->Payment->note),
			);
			
			$this->log('New order ID ' . $orderId . ' has been placed', 'ModuleIsotopeCheckout writeOrder()', TL_ACCESS);
			$salesEmail = $this->iso_sales_email ? $this->iso_sales_email : $GLOBALS['TL_ADMIN_EMAIL'];
			$this->Isotope->sendMail($this->iso_mail_admin, $salesEmail, $GLOBALS['TL_LANGUAGE'], $arrData);
			$this->Isotope->sendMail($this->iso_mail_customer, $this->Cart->billingAddress['email'], $GLOBALS['TL_LANGUAGE'], $arrData);
			
			$this->copyCartItems($this->Cart->id, $orderId);
			
			$this->Cart->delete();
			unset($_SESSION['CHECKOUT_DATA']);
			unset($_SESSION['isotope']);
		}
		
		return $strUniqueId;
	}
	
	/** 
	 * Copy items from the cart and place in the order items reference table.
	 * Also stores product downloads.
	 *
	 * @param integer $intCartId
	 * @param integer $intOrderId
	 * @return void
	 */
	protected function copyCartItems($intCartId, $intOrderId)
	{
		$intSorting = $this->Isotope->getNextSortValue('tl_iso_order_items');
		
		$objCartItems = $this->Database->prepare("SELECT * FROM tl_cart_items WHERE pid=?")->execute($intCartId);
		
		while( $objCartItems->next() )
		{
			$arrSet = array
			(
				'pid'				=> $intOrderId,
				'sorting'			=> $intSorting+128,
				'tstamp'			=> time(),
				'product_id'		=> $objCartItems->product_id,
				'quantity_sold'		=> $objCartItems->quantity_requested,
				'price'				=> $objCartItems->price,
				'product_options'	=> $objCartItems->product_options,
			);
			
			$itemId = $this->Database->prepare("INSERT INTO tl_iso_order_items %s")->set($arrSet)->execute()->insertId;
			
			$objDownloads = $this->Database->prepare("SELECT * FROM tl_product_downloads WHERE pid=?")->execute($objCartItems->product_id);
			
			while( $objDownloads->next() )
			{
				$arrSet = array
				(
					'pid'					=> $itemId,
					'tstamp'				=> time(),
					'download_id'			=> $objDownloads->id,
					'downloads_remaining'	=> ($objDownloads->downloads_allowed > 0 ? $objDownloads->downloads_allowed : ''),
				);
				
				$this->Database->prepare("INSERT INTO tl_iso_order_downloads %s")->set($arrSet)->execute();
			}
		}
	}
	
/*
	protected function getSelectedAddress($strStep = 'billing')
	{

		$intAddressId = $_SESSION['FORM_DATA'][$strStep.'_address'];

		// Take billing address
		if ($intAddressId == -1)
		{
			return array();
		}
		
		//gather from form
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
			
			$arrAddress['email'] = (strlen($arrAddress['email']) ? $arrAddress['email'] : $this->User->email);
			$arrAddress['phone'] = (strlen($arrAddress['phone']) ? $arrAddress['phone'] : $this->User->phone);
			
			$_SESSION['FORM_DATA'][$strStep . '_information_company'] = $arrAddress['company'];
			$_SESSION['FORM_DATA'][$strStep . '_information_firstname'] = $arrAddress['firstname'];
			$_SESSION['FORM_DATA'][$strStep . '_information_lastname'] = $arrAddress['lastname'];
			$_SESSION['FORM_DATA'][$strStep . '_information_street'] = $arrAddress['street'];
			$_SESSION['FORM_DATA'][$strStep . '_information_street_2'] = $arrAddress['street_2'];
			$_SESSION['FORM_DATA'][$strStep . '_information_street_3'] = $arrAddress['street_3'];
			$_SESSION['FORM_DATA'][$strStep . '_information_city'] = $arrAddress['city'];
			$_SESSION['FORM_DATA'][$strStep . '_information_state'] = $arrAddress['state'];
			$_SESSION['FORM_DATA'][$strStep . '_information_postal'] = $arrAddress['postal'];
			$_SESSION['FORM_DATA'][$strStep . '_information_country'] = $arrAddress['country'];			
			$_SESSION['FORM_DATA'][$strStep . '_information_email'] = $arrAddress['email'];
			$_SESSION['FORM_DATA'][$strStep . '_information_phone'] = $arrAddress['phone'];
		}
				
		return $arrAddress;
	}
*/
		
	
	
	protected function generateAddressWidget($field)
	{
		$strBuffer = '';
		$arrOptions = array();
		
		if (FE_USER_LOGGED_IN)
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=? ORDER BY isDefaultBilling DESC, isDefaultShipping DESC")->execute($this->User->id);
			
			while( $objAddress->next() )
			{
				if (!in_array($objAddress->country, $this->Isotope->Store->countries))
					continue;
					
				$arrOptions[] = array
				(
					'value'		=> $objAddress->id,
					'label'		=> $this->Isotope->generateAddressString($objAddress->row()),
				);
			}
		}
		
		switch($field)
		{
			case 'shipping_address':
				$arrAddress = $_SESSION['CHECKOUT_DATA'][$field] ? $_SESSION['CHECKOUT_DATA'][$field] : $this->Cart->shippingAddress;
				$intDefaultValue = $arrAddress['id'] ? $arrAddress['id'] : 0;
				
				array_insert($arrOptions, 0, array(array
				(
					'value'	=> -1,
					'label' => &$GLOBALS['TL_LANG']['useBillingAddress'],
				)));
				
				$arrOptions[] = array
				(
					'value'	=> 0,
					'label' => &$GLOBALS['TL_LANG']['differentShippingAddress'],
				);

				break;
				
			case 'billing_address':
			default:
				$arrAddress = $_SESSION['CHECKOUT_DATA'][$field] ? $_SESSION['CHECKOUT_DATA'][$field] : $this->Cart->billingAddress;
				$intDefaultValue = $arrAddress['id'] ? $arrAddress['id'] : 0;
				
				if (FE_USER_LOGGED_IN)
				{
					$arrOptions[] = array
					(
						'value'	=> 0,
						'label' => &$GLOBALS['TL_LANG']['createNewAddressLabel'],
					);
				}
				break;
		}
	
		if (count($arrOptions))
		{
			$strClass = $GLOBALS['TL_FFL']['radio'];
			
			$objWidget = new $strClass(array('id'=>$field, 'name'=>$field, 'required'=>true));
			$objWidget->options = $arrOptions;
			$objWidget->value = $intDefaultValue;
			$objWidget->onclick = "Isotope.toggleAddressFields(this, '" . $field . "_new');";
					
			$objWidget->storeValues = true;

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && strlen($this->Input->post($field)))
			{
				$objWidget->validate();
				
				if ($objWidget->hasErrors())
				{
					$this->doNotSubmit = true;
				}
				else
				{
					$_SESSION['CHECKOUT_DATA'][$field]['id'] = $objWidget->value;
				}
			}
			
			$strBuffer .= $objWidget->parse();
		}
		
		if (strlen($_SESSION['CHECKOUT_DATA'][$field]['id']))
		{
			$this->Cart->$field = $_SESSION['CHECKOUT_DATA'][$field]['id'];
		}
		
		$strBuffer .= '<div id="' . $field . '_new"' . (((!FE_USER_LOGGED_IN && $field == 'billing_address') || $objWidget->value == 0) ? '' : ' style="display:none">');
		$strBuffer .= '<span>' . $this->getCurrentStepWidgets('tl_address_book', $field) . '</span>';
		$strBuffer .= '</div>';
		
		return $strBuffer;
	}
	
	
	/**
	 * Generate the current step widgets.
	 * strResourceTable is used either to load a DCA or else to gather settings related to a given DCA.
	 */
	protected function getCurrentStepWidgets($strResourceTable, $strAddressField)
	{
		$objTemplate = new FrontendTemplate('fields_insert');
		
		$this->loadLanguageFile($strResourceTable);
		$this->loadDataContainer($strResourceTable);
		
		$arrAddress = array('id'=>0);

		foreach( $this->Isotope->Store->address_fields as $field )
		{
			$arrData = $GLOBALS['TL_DCA'][$strResourceTable]['fields'][$field];
			
			if (!is_array($arrData))
				continue;
			
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
			
			// Continue if the class is not defined
			if (!$this->classFileExists($strClass) || !$arrData['eval']['isoEditable'] || !count($arrData['eval']['isoCheckoutGroups']) || !in_array($strAddressField, $arrData['eval']['isoCheckoutGroups']))
				continue;
			
			// Special field "country"
			if ($field == 'country')
			{
				$arrCountries = array_combine(array_values($this->Isotope->Store->countries), array_keys($this->Isotope->Store->countries));
				
				$arrData['options'] = array_intersect_key($arrData['options'], $arrCountries);
				$arrData['default'] = $this->Isotope->Store->country;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, $strAddressField . '_' . $field, (strlen($_SESSION['CHECKOUT_DATA'][$strAddressField][$field]) ? $_SESSION['CHECKOUT_DATA'][$strAddressField][$field] : (strlen($this->User->$field) ? $this->User->$field : $arrData['default']))));
			
			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
			
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && $this->Input->post($strAddressField) === '0')
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
					$arrAddress[$field] = $varValue;
				}
			}

			$objTemplate->fields .= $objWidget->parse();
		}
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && $this->Input->post($strAddressField) === '0' && !$this->doNotSubmit)
		{
			$_SESSION['CHECKOUT_DATA'][$strAddressField] = $arrAddress;
		}
		
		if ($_SESSION['CHECKOUT_DATA'][$strAddressField] && strlen($_SESSION['CHECKOUT_DATA'][$strAddressField]['id']) && $_SESSION['CHECKOUT_DATA'][$strAddressField]['id'] == 0)
		{
			$this->Cart->$strAddressField = $_SESSION['CHECKOUT_DATA'][$strAddressField];
		}

		return $objTemplate->parse();	
	}
}

