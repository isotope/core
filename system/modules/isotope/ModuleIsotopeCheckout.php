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
	
	protected $arrCheckoutInfo;
	
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
		
		
		if (!$this->iso_forward_review && !strlen($this->Input->get('step')))
		{
			$this->redirectToNextStep();
		}

		
		// Default template settings. Must be set at beginning so they can be overwritten later (eg. trough callback)
		$this->Template->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->formId = $this->strFormId;
		$this->Template->formSubmit = $this->strFormId;
		$this->Template->enctype = 'application/x-www-form-urlencoded';
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
			$this->strFormId = 'iso_mod_checkout_' . $step;
			$this->Template->formId = $this->strFormId;
			$this->Template->formSubmit = $this->strFormId;
			
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
					$strBuffer .= $this->{$callback[0]}->{$callback[1]}(&$this);
				}
			
				if ($this->doNotSubmit && $step != $this->strCurrentStep)
				{			
					$this->redirect($this->addToUrl('step=' . $step));
				}
			
			}
			
			if ($step == $this->strCurrentStep)
				break;
		}
		
		if ($this->strCurrentStep == 'process')
		{
			$strBuffer = $this->Cart->hasPayment ? $this->Cart->Payment->checkoutForm() : false;
			
			if ($strBuffer === false)
			{
				$this->redirect($this->addToUrl('step=complete'));
			}
			
			$this->Template->showForm = false;
			$this->doNotSubmit = true;
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
				$this->Template->showPrevious = true;
				$this->redirect($this->addToUrl('step=failed'));
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
			$intKey = array_search($this->strCurrentStep, $arrSteps) + 1;
			
			if ($intKey == count($arrSteps))
			{
				$this->redirect($this->addToUrl('step=process'));
			}
			else
			{
				$this->redirect($this->addToUrl('step='.$arrSteps[$intKey]));
			}
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
	
	
	protected function getBillingAddressInterface($blnReview=false)
	{
		if ($blnReview)
		{
			return array
			(
				'billing_address' => array
				(
					'headline'	=> ($this->Cart->shippingAddress['id'] == -1 ? $GLOBALS['TL_LANG']['ISO']['billing_shipping_address'] : $GLOBALS['TL_LANG']['ISO']['billing_address']),
					'info'		=> $this->Isotope->generateAddressString($this->Cart->billingAddress),
					'edit'		=> $this->addToUrl('step=address'),
				),
			);
		}
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'billing_address');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['billing_address'];
		$objTemplate->message = (FE_USER_LOGGED_IN ? $GLOBALS['TL_LANG']['ISO']['billing_address_message'] : $GLOBALS['TL_LANG']['ISO']['billing_address_guest_message']);
		$objTemplate->fields = $this->generateAddressWidget('billing_address');
		
		return $objTemplate->parse();
	}
	
	
	protected function getShippingAddressInterface($blnReview=false)
	{
		if (!$this->Cart->requiresShipping)
			return '';
			
		if ($blnReview)
		{
			if ($this->Cart->shippingAddress['id'] == -1)
				return false;
				
			return array
			(
				'shipping_address' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['shipping_address'],
					'info'		=> $this->Isotope->generateAddressString($this->Cart->shippingAddress),
					'edit'		=> $this->addToUrl('step=address'),
				),
			);
		}
			
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'shipping_address');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['shipping_address'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['shipping_address_message'];
		$objTemplate->fields =  $this->generateAddressWidget('shipping_address');
		
		return $objTemplate->parse();
	}
	
	
	protected function getShippingModulesInterface($blnReview=false)
	{
		if ($blnReview)
		{
			if (!$this->Cart->hasShipping)
				return false;
				
			return array
			(
				'shipping_method' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['shipping_method'],
					'info'		=> $this->Cart->Shipping->checkoutReview(),
					'edit'		=> $this->addToUrl('step=shipping'),
				),
			);
		}
		
		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_shipping_modules);
	
		if (is_array($arrModuleIds) && count($arrModuleIds))
		{
			$arrData = ($this->Input->post('shipping') ? $this->Input->post('shipping') : $_SESSION['CHECKOUT_DATA']['shipping']);
			
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
										 ($this->Cart->Shipping->id == $objModule->id || $objModules->numRows==1 ? ' checked="checked"' : ''),
										 $objModule->id,
	 									 $objModule->label,
	 									 $this->Isotope->formatPriceWithCurrency($objModule->price), 
	 									 ($objModule->note ? '<div class="clearBoth"></div><br /><div class="shippingNote"><strong>Note:</strong><br />' . $objModule->note . '</div>' : ''),
	 									 ($objModule->getShippingOptions($objModule->id) ? '<div class="clearBoth"></div><br /><div class="shippingOptions"><strong>Options:</strong><br />' . $objModule->getShippingOptions($objModule->id) . '</div>' : ''));
	 									 
	 			$objLastModule = $objModule;
			}
		}
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'shipping_method');
				
		if(!count($arrModules))
		{			
			$this->doNotSubmit = true;
			$this->Template->showNext = false;
			
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->class = 'shipping_method';
			$objTemplate->hl = 'h2';
			$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['shipping_method'];
			$objTemplate->type = 'error';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noShippingModules'];
			return $objTemplate->parse();
		}
		elseif (!$this->Cart->hasShipping && !strlen($_SESSION['CHECKOUT_DATA']['shipping']['module']) && count($arrModules) == 1)
		{
			$this->Cart->Shipping = $objLastModule;
			$_SESSION['CHECKOUT_DATA']['shipping']['module'] = $this->Cart->Shipping->id;
		}
		elseif (!$this->Cart->hasShipping)
		{
			if (count($_POST))
			{
				$objTemplate->error = $GLOBALS['TL_LANG']['ISO']['shipping_method_missing'];
			}
			
			$this->doNotSubmit = true;
		}
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['shipping_method'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['shipping_method_message'];
		$objTemplate->shippingMethods = $arrModules;

		return $objTemplate->parse();	
	}
	
	
	protected function getPaymentModulesInterface($blnReview=false)
	{
		if ($blnReview)
		{
			if (!$this->Cart->hasPayment)
				return false;
			
			return array
			(
				'payment_method' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['payment_method'],
					'info'		=> $this->Cart->Payment->checkoutReview(),
					'edit'		=> $this->addToUrl('step=payment'),
				),
			);
		}
		
		
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
	 			
	 			// Custom payment fields for module
	 			$strForm = $objModule->paymentForm($this);
	 			if (strlen($strForm))
	 			{
	 				$strForm = '<div class="payment_data" id="payment_data_' . $objModule->id . '">' . $strForm . '</div>';
	 			}
							
				$arrModules[] = sprintf('<input id="ctrl_payment_module_%s" type="radio" class="radio payment_module" name="payment[module]" value="%s"%s /> <label for="ctrl_payment_module_%s">%s</label>%s',
										 $objModule->id,
										 $objModule->id,
										 ($this->Cart->Payment->id == $objModule->id || $objModules->numRows==1 ? ' checked="checked"' : ''),
										 $objModule->id,
	 									 $objModule->label,
	 									 $strForm);
	 									 
	 			$objLastModule = $objModule;
			}
		}
		
		$objTemplate = new FrontendTemplate($this->strStepTemplateBaseName . 'payment_method');
		
		if(!count($arrModules))
		{
			$this->doNotSubmit = true;
			$this->Template->showNext = false;
			
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->class = 'payment_method';
			$objTemplate->hl = 'h2';
			$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['payment_method'];
			$objTemplate->type = 'error';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];
			return $objTemplate->parse();
		}
		elseif (!$this->Cart->hasPayment && !strlen($_SESSION['CHECKOUT_DATA']['payment']['module']) && count($arrModules) == 1)
		{
			$this->Cart->Payment = $objLastModule;
			$_SESSION['CHECKOUT_DATA']['payment']['module'] = $this->Cart->Payment->id;
		}
		elseif (!$this->Cart->hasPayment)
		{
			if (count($_POST))
			{
				$objTemplate->error = $GLOBALS['TL_LANG']['ISO']['payment_method_missing'];
			}
			
			$this->doNotSubmit = true;
		}

		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['payment_method'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['payment_method_message'];
		$objTemplate->paymentMethods = $arrModules;
	
		return $objTemplate->parse();
	}
	
	
	protected function getOrderConditionsInterface($blnReview=false)
	{
		if (!$this->iso_order_conditions || $blnReview)
			return '';
		
		$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")->limit(1)->execute($this->iso_order_conditions);
		
		$hasUpload = false;
		$arrSubmitted = array();

		$this->loadDataContainer('tl_form_field');

		$strFields = '';
		$strHidden = '';

//		$this->getMaxFileSize();

		// Get all form fields
		$objFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? ORDER BY sorting")
									->execute($objForm->id);

		$row = 0;
		$max_row = $objFields->numRows;

		while ($objFields->next())
		{
			$strClass = $GLOBALS['TL_FFL'][$objFields->type];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$arrData = $objFields->row();

			$arrData['decodeEntities'] = true;
			$arrData['allowHtml'] = $objForm->allowTags;
			$arrData['rowClass'] = 'row_'.$row . (($row == 0) ? ' row_first' : (($row == ($max_row - 1)) ? ' row_last' : '')) . ((($row % 2) == 0) ? ' even' : ' odd');
			$arrData['tableless'] = true;

			// Increase the row count if its a password field
			if ($objFields->type == 'password')
			{
				++$row;
				++$max_row;

				$arrData['rowClassConfirm'] = 'row_'.$row . (($row == ($max_row - 1)) ? ' row_last' : '') . ((($row % 2) == 0) ? ' even' : ' odd');
			}

			$objWidget = new $strClass($arrData);
			$objWidget->required = $objFields->mandatory ? true : false;

			// HOOK: load form field callback
			if (isset($GLOBALS['TL_HOOKS']['loadFormField']) && is_array($GLOBALS['TL_HOOKS']['loadFormField']))
			{
				foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback)
				{
					$this->import($callback[0]);
					$objWidget = $this->$callback[0]->$callback[1]($objWidget, $this->strFormId, $objForm->row());
				}
			}

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
			{
				$objWidget->validate();

				// HOOK: validate form field callback
				if (isset($GLOBALS['TL_HOOKS']['validateFormField']) && is_array($GLOBALS['TL_HOOKS']['validateFormField']))
				{
					foreach ($GLOBALS['TL_HOOKS']['validateFormField'] as $callback)
					{
						$this->import($callback[0]);
						$objWidget = $this->$callback[0]->$callback[1]($objWidget, $this->strFormId, $objForm->row());
					}
				}

				if ($objWidget->hasErrors())
				{
					$this->doNotSubmit = true;
				}

				// Store current value in the session
				elseif ($objWidget->submitInput())
				{
					$arrSubmitted[$objFields->name] = $objWidget->value;
				}

				unset($_POST[$objFields->name]);
			}

			if ($objWidget instanceof uploadable)
			{
				$hasUpload = true;
			}

			if ($objWidget instanceof FormHidden)
			{
				$strHidden .= $objWidget->parse();
				--$max_row;
				continue;
			}

			$strFields .= $objWidget->parse();
			++$row;
		}

		$strAttributes = '';
		$arrAttributes = deserialize($objForm->attributes, true);

		if (strlen($arrAttributes[1]))
		{
			$strAttributes .= ' ' . $arrAttributes[1];
		}

		$this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

		return '<div class="order_conditions' . $strAttributes . '">' . $strHidden . $strFields . '</div>';
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
				'product_options'	=> $objProduct->product_options
			));
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
		
		
		$objTemplate->info = $this->getCheckoutInfo();
		$objTemplate->products = $arrProductData;
		$objTemplate->surcharges = $arrSurcharges;
		$objTemplate->edit_info = $GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo'];
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$objTemplate->subTotalPrice = $this->generatePrice($this->Cart->subTotal);
		$objTemplate->grandTotalPrice = $this->generatePrice($this->Cart->grandTotal, 'stpl_total_price');
		
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
			'checkout_info'			=> $this->getCheckoutInfo(),
			
			'status'				=> ($blnCheckout ? $this->Cart->Payment->new_order_status : ''),
			'language'				=> $GLOBALS['TL_LANGUAGE'],
			'billing_address'		=> serialize($this->Cart->billingAddress),
			'shipping_address'		=> serialize($this->Cart->shippingAddress),
			'currency'				=> $this->Isotope->Store->currency
		);
				
		
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
			$strShippingAddress = $this->Cart->shippingAddress['id'] == -1 ? $GLOBALS['TL_LANG']['useBillingAddress'] : $this->Isotope->generateAddressString($this->Cart->shippingAddress);

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
			
			foreach( $this->Cart->billingAddress as $k => $v )
			{
				$arrData['billing_'.$k] = $v;
			}
			
			foreach( $this->Cart->shippingAddress as $k => $v )
			{
				$arrData['shipping_'.$k] = $v;
			}
			
			$this->log('New order ID ' . $orderId . ' has been placed', 'ModuleIsotopeCheckout writeOrder()', TL_ACCESS);
			$salesEmail = $this->iso_sales_email ? $this->iso_sales_email : $GLOBALS['TL_ADMIN_EMAIL'];
			$this->Isotope->sendMail($this->iso_mail_admin, $salesEmail, $GLOBALS['TL_LANGUAGE'], $arrData);
			$this->Isotope->sendMail($this->iso_mail_customer, $this->Cart->billingAddress['email'], $GLOBALS['TL_LANGUAGE'], $arrData);
			
			$this->copyCartItems($orderId);
			
			$this->Cart->delete();
			unset($_SESSION['CHECKOUT_DATA']);
			unset($_SESSION['isotope']);
		}
		
		return $strUniqueId;
	}
	
	
	/** 
	 * Copy items from the cart and place in the order items reference table. Also stores product downloads.
	 *
	 * @param integer $intCartId
	 * @param integer $intOrderId
	 * @return void
	 */
	protected function copyCartItems($intOrderId)
	{
		$arrProducts = $this->Cart->getProducts();
		
		foreach( $arrProducts as $objProduct )
		{
			$arrSet = array
			(
				'pid'				=> $intOrderId,
				'tstamp'			=> time(),
				'product_id'		=> $objProduct->id,
				'quantity_sold'		=> $objProduct->quantity_requested,
				'price'				=> $objProduct->price,
				'product_options'	=> $objProduct->product_options,
				'product_data'		=> serialize($objProduct),
			);
			
			$itemId = $this->Database->prepare("INSERT INTO tl_iso_order_items %s")->set($arrSet)->execute()->insertId;
			
			$objDownloads = $this->Database->prepare("SELECT * FROM tl_product_downloads WHERE pid=?")->execute($objProduct->id);
			
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
				$intDefaultValue = strlen($arrAddress['id']) ? $arrAddress['id'] : -1;

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
				$intDefaultValue = strlen($arrAddress['id']) ? $arrAddress['id'] : 0;
				
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
		elseif (!FE_USER_LOGGED_IN)
		{
			
		//	$this->doNotSubmit = true;
		}
		
		
		$strBuffer .= '<div id="' . $field . '_new" class="address_new"' . (((!FE_USER_LOGGED_IN && $field == 'billing_address') || $objWidget->value == 0) ? '' : ' style="display:none">');
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
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && ($this->Input->post($strAddressField) === '0' || !$this->Input->post($strAddressField)))
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
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit && is_array($arrAddress) && count($arrAddress))
		{
			$arrAddress['id'] = 0;
			$_SESSION['CHECKOUT_DATA'][$strAddressField] = $arrAddress;		
		}

		if (is_array($_SESSION['CHECKOUT_DATA'][$strAddressField]) && $_SESSION['CHECKOUT_DATA'][$strAddressField]['id'] === 0)
		{
			$this->Cart->$strAddressField = $_SESSION['CHECKOUT_DATA'][$strAddressField];
		}
	
		
		return $objTemplate->parse();	
	}
	
	
	protected function getCheckoutInfo()
	{
		if (!is_array($this->arrCheckoutInfo))
		{
			// Run trough all steps to collect checkout information
			$arrCheckoutInfo = array();
			foreach( $GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks )
			{
				if ($step == 'review')
					continue;
				
				foreach( $arrCallbacks as $callback )
				{
					if ($callback[0] == 'ModuleIsotopeCheckout')
					{
						$arrInfo = $this->{$callback[1]}(true);
					}
					else
					{
						$this->import($callback[0]);
						$arrInfo = $this->{$callback[0]}->{$callback[1]}(&$this, true);
					}
					
					if (is_array($arrInfo) && count($arrInfo))
					{
						$arrCheckoutInfo += $arrInfo;
					}
				}
			}
			
			reset($arrCheckoutInfo);
			$arrCheckoutInfo[key($arrCheckoutInfo)]['class'] .= ' first';
			end($arrCheckoutInfo);
			$arrCheckoutInfo[key($arrCheckoutInfo)]['class'] .= ' last';
			
			$this->arrCheckoutInfo = $arrCheckoutInfo;
		}
		
		return $this->arrCheckoutInfo;
	}
}

