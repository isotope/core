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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeCheckout extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_checkout';
	
	/**
	 * Current step
	 * @var string
	 */
	protected $strCurrentStep;
	
	/**
	 * Checkout info. Contains an overview about each step to show on the review page (eg. address, payment & shipping method).
	 * @var array
	 */
	protected $arrCheckoutInfo;
	
	/**
	 * Form ID
	 * @var string
	 */
	protected $strFormId = 'iso_mod_checkout';
	
	/**
	 * Order data. Each checkout step can provide key-value (string) data for the order email.
	 * @var array
	 */
	public $arrOrderData = array();
	
	/**
	 * Do not submit form
	 * @var bool
	 */
	public $doNotSubmit = false;


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
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;

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
		if (!$this->Isotope->Cart->items)
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
		if (!$this->Isotope->Cart->requiresShipping)
		{
			unset($GLOBALS['ISO_CHECKOUT_STEPS']['shipping']);
			
			// Remove payment step if items are free of charge. We need to do this here because shipping might have a price.
			if (!$this->Isotope->Cart->requiresPayment)
			{
				unset($GLOBALS['ISO_CHECKOUT_STEPS']['payment']);
			}
		}
		
		
		if ($this->strCurrentStep == 'failed')
		{
			$this->Database->prepare("UPDATE tl_iso_orders SET status='on_hold' WHERE cart_id=?")->execute($this->Isotope->Cart->id);
			$this->Template->mtype = 'error';
			$this->Template->message = strlen($this->Input->get('reason')) ? $this->Input->get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
			$this->strCurrentStep = 'review';
		}
		
		
		// Run trough all steps until we find the current one or one reports failure
		foreach( $GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks )
		{
			// Step could be removed while looping
			if (!isset($GLOBALS['ISO_CHECKOUT_STEPS'][$step]))
				continue;
				
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
		
		if ($this->strCurrentStep == 'process')
		{
			$this->writeOrder(false);
			
			$strBuffer = $this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->checkoutForm() : false;
			
			if ($strBuffer === false)
			{
				$this->redirect($this->addToUrl('step=complete'));
			}
			
			$this->Template->showForm = false;
			$this->doNotSubmit = true;
		}
		
		if ($this->strCurrentStep == 'complete')
		{
			$strBuffer = $this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->processPayment() : true;
				
			if ($strBuffer === true)
			{
				unset($_SESSION['FORM_DATA']);
				unset($_SESSION['FILES']);
				$strUniqueId = $this->writeOrder(true);
				
				if ($strUniqueId === false)
				{
					$this->redirect($this->addToUrl('step=failed'));
				}
				
				$this->redirect($this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->orderCompleteJumpTo)->fetchAssoc()) . '?uid='.$strUniqueId);
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
		$blnRequiresPayment = $this->Isotope->Cart->requiresPayment;
		
		if ($blnReview)
		{
			return array
			(
				'billing_address' => array
				(
					'headline'	=> ($blnRequiresPayment ? ($this->Isotope->Cart->shippingAddress['id'] == -1 ? $GLOBALS['TL_LANG']['ISO']['billing_shipping_address'] : $GLOBALS['TL_LANG']['ISO']['billing_address']) : (($this->Isotope->Cart->hasShipping && $this->Isotope->Cart->shippingAddress['id'] == -1) ? $GLOBALS['TL_LANG']['ISO']['shipping_address'] : $GLOBALS['TL_LANG']['ISO']['customer_address'])),
					'info'		=> $this->Isotope->generateAddressString($this->Isotope->Cart->billingAddress, $this->Isotope->Config->billing_fields),
					'edit'		=> $this->addToUrl('step=address'),
				),
			);
		}
		
		$objTemplate = new IsotopeTemplate('iso_checkout_billing_address');
		
		$objTemplate->headline = $blnRequiresPayment ? $GLOBALS['TL_LANG']['ISO']['billing_address'] : $GLOBALS['TL_LANG']['ISO']['customer_address'];
		$objTemplate->message = (FE_USER_LOGGED_IN ? $GLOBALS['TL_LANG']['ISO'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_message'] : $GLOBALS['TL_LANG']['ISO'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_guest_message']);
		$objTemplate->fields = $this->generateAddressWidget('billing_address');
		
		if (!$this->doNotSubmit)
		{
			$strBillingAddress = $this->Isotope->generateAddressString($this->Isotope->Cart->billingAddress, $this->Isotope->Config->billing_fields);
			
			$this->arrOrderData['billing_address']		= $strBillingAddress;
			$this->arrOrderData['billing_address_text']	= str_replace('<br />', "\n", $strBillingAddress);
		}
		
		return $objTemplate->parse();
	}
	
	
	protected function getShippingAddressInterface($blnReview=false)
	{
		if (!$this->Isotope->Cart->requiresShipping)
			return '';
			
		if ($blnReview)
		{
			if ($this->Isotope->Cart->shippingAddress['id'] == -1)
				return false;
				
			return array
			(
				'shipping_address' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['shipping_address'],
					'info'		=> $this->Isotope->generateAddressString($this->Isotope->Cart->shippingAddress, $this->Isotope->Config->shipping_fields),
					'edit'		=> $this->addToUrl('step=address'),
				),
			);
		}
			
		$objTemplate = new IsotopeTemplate('iso_checkout_shipping_address');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['shipping_address'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['shipping_address_message'];
		$objTemplate->fields =  $this->generateAddressWidget('shipping_address');
		
		if (!$this->doNotSubmit)
		{
			$strShippingAddress = $this->Isotope->Cart->shippingAddress['id'] == -1 ? ($this->Isotope->Cart->requiresPayment ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress']) : $this->Isotope->generateAddressString($this->Isotope->Cart->shippingAddress, $this->Isotope->Config->shipping_fields);
			
			$this->arrOrderData['shipping_address']			= $strShippingAddress;
			$this->arrOrderData['shipping_address_text']	= str_replace('<br />', "\n", $strShippingAddress);
		}
		
		return $objTemplate->parse();
	}
	
	
	protected function getShippingModulesInterface($blnReview=false)
	{
		if ($blnReview)
		{
			if (!$this->Isotope->Cart->hasShipping)
				return false;
				
			return array
			(
				'shipping_method' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['shipping_method'],
					'info'		=> $this->Isotope->Cart->Shipping->checkoutReview(),
					'edit'		=> $this->addToUrl('step=shipping'),
				),
			);
		}
		
		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_shipping_modules);
	
		if (is_array($arrModuleIds) && count($arrModuleIds))
		{
			$arrData = ($this->Input->post('shipping') ? $this->Input->post('shipping') : $_SESSION['CHECKOUT_DATA']['shipping']);
			
			$objModules = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id IN (" . implode(',', $arrModuleIds) . ") AND enabled='1'");
			
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
	 				$this->Isotope->Cart->Shipping = $objModule;
	 			}
	 			
	 			$fltPrice = $objModule->price;
	 			$strSurcharge = $objModule->surcharge;
	 			$strPrice = $fltPrice != 0 ? (($strSurcharge == '' ? '' : ' ('.$strSurcharge.')') . ': '.$this->Isotope->formatPriceWithCurrency($fltPrice)) : '';
				
				$arrModules[] = sprintf('<input id="ctrl_shipping_module_%s" type="radio" name="shipping[module]" value="%s"%s /> <label for="ctrl_shipping_module_%s">%s%s</label>%s%s',
										 $objModule->id,
										 $objModule->id,
										 (($this->Isotope->Cart->Shipping->id == $objModule->id || $objModules->numRows==1) ? ' checked="checked"' : ''),
										 $objModule->id,
	 									 $objModule->label,
	 									 $strPrice,
	 									 ($objModule->note ? '<div class="clear">&nbsp;</div><br /><div class="shippingNote"><strong>Note:</strong><br />' . $objModule->note . '</div>' : ''),
	 									 ($objModule->getShippingOptions($objModule->id) ? '<div class="clear">&nbsp;</div><br /><div class="shippingOptions"><strong>Options:</strong><br />' . $objModule->getShippingOptions($objModule->id) . '</div>' : ''));
	 									 
	 			$objLastModule = $objModule;
			}
		}
		
		$objTemplate = new IsotopeTemplate('iso_checkout_shipping_method');
				
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
		elseif (!$this->Isotope->Cart->hasShipping && !strlen($_SESSION['CHECKOUT_DATA']['shipping']['module']) && count($arrModules) == 1)
		{
			$this->Isotope->Cart->Shipping = $objLastModule;
			$_SESSION['CHECKOUT_DATA']['shipping']['module'] = $this->Isotope->Cart->Shipping->id;
		}
		elseif (!$this->Isotope->Cart->hasShipping)
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
		
		if (!$this->doNotSubmit)
		{
			$this->arrOrderData['shipping_method_id']	= $this->Isotope->Cart->Shipping->id;
			$this->arrOrderData['shipping_method']		= $this->Isotope->Cart->Shipping->label;
			$this->arrOrderData['shipping_note']		= $this->Isotope->Cart->Shipping->note;
			$this->arrOrderData['shipping_note_text']	= strip_tags($this->Isotope->Cart->Shipping->note);
		}
		
		// Remove payment step if items are free of charge
		if (!$this->Isotope->Cart->requiresPayment)
		{
			unset($GLOBALS['ISO_CHECKOUT_STEPS']['payment']);
		}

		return $objTemplate->parse();	
	}
	
	
	protected function getPaymentModulesInterface($blnReview=false)
	{
		if ($blnReview)
		{
			if (!$this->Isotope->Cart->hasPayment)
				return false;
			
			return array
			(
				'payment_method' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['payment_method'],
					'info'		=> $this->Isotope->Cart->Payment->checkoutReview(),
					'note'		=> $this->Isotope->Cart->Payment->note,
					'edit'		=> $this->addToUrl('step=payment'),
				),
			);
		}
		
		
		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_payment_modules);
		
		if (is_array($arrModuleIds) && count($arrModuleIds))
		{
			$arrData = $this->Input->post('payment');
			$objModules = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE id IN (" . implode(',', $arrModuleIds) . ") AND enabled='1'");
			
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
	 				$this->Isotope->Cart->Payment = $objModule;
	 			}
	 			
	 			// Custom payment fields for module
	 			$strForm = $objModule->paymentForm($this);
	 			if (strlen($strForm))
	 			{
	 				$strForm = '<div class="payment_data" id="payment_data_' . $objModule->id . '">' . $strForm . '</div>';
	 			}
	 			
	 			$fltPrice = $objModule->price;
	 			$strSurcharge = $objModule->surcharge;
	 			$strPrice = $fltPrice != 0 ? (($strSurcharge == '' ? '' : ' ('.$strSurcharge.')') . ': '.$this->Isotope->formatPriceWithCurrency($fltPrice)) : '';
							
				$arrModules[] = sprintf('<input id="ctrl_payment_module_%s" type="radio" class="radio payment_module" name="payment[module]" value="%s"%s /> <label for="ctrl_payment_module_%s">%s%s</label>%s',
										 $objModule->id,
										 $objModule->id,
										 (($this->Isotope->Cart->Payment->id == $objModule->id || $objModules->numRows==1) ? ' checked="checked"' : ''),
										 $objModule->id,
	 									 $objModule->label,
	 									 $strPrice,
	 									 $strForm);
	 									 
	 			$objLastModule = $objModule;
			}
		}
		
		$objTemplate = new IsotopeTemplate('iso_checkout_payment_method');
		
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
		elseif (!$this->Isotope->Cart->hasPayment && !strlen($_SESSION['CHECKOUT_DATA']['payment']['module']) && count($arrModules) == 1)
		{
			$this->Isotope->Cart->Payment = $objLastModule;
			$_SESSION['CHECKOUT_DATA']['payment']['module'] = $this->Isotope->Cart->Payment->id;
		}
		elseif (!$this->Isotope->Cart->hasPayment)
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
		
		if (!$this->doNotSubmit)
		{
			$this->arrOrderData['payment_method_id']	= $this->Isotope->Cart->Payment->id;
			$this->arrOrderData['payment_method']		= $this->Isotope->Cart->Payment->label;
			$this->arrOrderData['payment_note']			= $this->Isotope->Cart->Payment->note;
			$this->arrOrderData['payment_note_text']	= strip_tags($this->Isotope->Cart->Payment->note);
		}
	
		return $objTemplate->parse();
	}
	
	
	protected function getOrderConditionsInterface($blnReview=false)
	{
		if (!$this->iso_order_conditions)
			return '';
			
		if ($blnReview)
		{
			if (!$this->doNotSubmit)
			{
				if (is_array($_SESSION['FORM_DATA']))
				{
					foreach( $_SESSION['FORM_DATA'] as $name => $value )
					{
						$this->arrOrderData['form_'.$name] = $value;
					}
				}
				
				if (is_array($_SESSION['FILES']))
				{
					foreach( $_SESSION['FILES'] as $name => $file )
					{
						$this->arrOrderData['form_'.$name] = $this->Environment->base . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
					}
				}
			}
			
			return '';
		}
		
		$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")->limit(1)->execute($this->iso_order_conditions);
		
		$hasUpload = false;

		$this->loadDataContainer('tl_form_field');

		$strFields = '';
		$strHidden = '';

		// Get all form fields
		$objFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? AND invisible='' ORDER BY sorting")
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
					$_SESSION['FORM_DATA'][$objFields->name] = $objWidget->value;
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
		
		if (!$this->doNotSubmit)
		{
			if (is_array($_SESSION['FORM_DATA']))
			{
				foreach( $_SESSION['FORM_DATA'] as $name => $value )
				{
					$this->arrOrderData['form_'.$name] = $value;
				}
			}
			
			if (is_array($_SESSION['FILES']))
			{
				foreach( $_SESSION['FILES'] as $name => $file )
				{
					$this->arrOrderData['form_'.$name] = $this->Environment->base . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
				}
			}
		}

		return '<div class="order_conditions' . $strAttributes . '">' . $strHidden . $strFields . '</div>';
	}
	
	
	protected function getOrderReviewInterface($blnReview=false)
	{
		if ($blnReview)
			return;
			
		$objTemplate = new IsotopeTemplate('iso_checkout_order_review');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['order_review'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['order_review_message'];
		
		// Surcharges must be initialized before getProducts() to apply tax_id to each product
		$arrSurcharges = array();
		foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge )
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
		$arrProducts = $this->Isotope->Cart->getProducts();
		foreach( $arrProducts as $objProduct )
		{
			$arrProductData[] = array_merge($objProduct->getAttributes(), array
			(
				'id'				=> $objProduct->id,
				'image'				=> $objProduct->images->main_image,
				'link'				=> $objProduct->href_reader,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
				'total_price'		=> $this->Isotope->formatPriceWithCurrency($objProduct->total_price),
				'quantity'			=> $objProduct->quantity_requested,
				'tax_id'			=> $objProduct->tax_id,
				'product_options'	=> $objProduct->getOptions(),
			));
		}
		
		$objTemplate->info = $this->getCheckoutInfo();
		$objTemplate->products = $arrProductData;
		$objTemplate->surcharges = $arrSurcharges;
		$objTemplate->edit_info = $GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo'];
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal);
		$objTemplate->grandTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal);
		
		return $objTemplate->parse();
	}

	
	protected function writeOrder($blnCheckout=false)
	{
		$time = time();
		$strUniqueId = uniqid($this->Isotope->Config->orderPrefix, true);
	
		$arrSet = array
		(
			'pid'					=> (FE_USER_LOGGED_IN ? $this->User->id : 0),
			'tstamp'				=> $time,
			'date'					=> $time,
			'uniqid'				=> $strUniqueId,
			'config_id'				=> $this->Isotope->Config->id,
			'cart_id'				=> $this->Isotope->Cart->id,
			'shipping_id'			=> ($this->Isotope->Cart->hasShipping ? $this->Isotope->Cart->Shipping->id : 0),
			'payment_id'			=> ($this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->id : 0),
			'subTotal'				=> $this->Isotope->Cart->subTotal,		// + ($this->Input->post('gift_wrap') ? 10 : 0),		
			'taxTotal'	 			=> $this->Isotope->Cart->taxTotal,
			'shippingTotal'			=> $this->Isotope->Cart->shippingTotal,
			'grandTotal'			=> $this->Isotope->Cart->grandTotal,
			'surcharges'			=> $this->Isotope->Cart->getSurcharges(),
			'checkout_info'			=> $this->getCheckoutInfo(),
			
			'status'				=> ($blnCheckout ? ($this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->new_order_status : 'pending') : ''),
			
			'language'				=> $GLOBALS['TL_LANGUAGE'],
			'billing_address'		=> serialize($this->Isotope->Cart->billingAddress),
			'shipping_address'		=> serialize($this->Isotope->Cart->shippingAddress),
			'currency'				=> $this->Isotope->Config->currency
		);
				
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->execute($this->Isotope->Cart->id);
	
		if (!$objOrder->numRows)
		{
			$orderId = $this->Database->prepare("INSERT INTO tl_iso_orders %s")
									  ->set($arrSet)
									  ->executeUncached()
									  ->insertId;
									   
			$this->Database->prepare("UPDATE tl_iso_orders SET order_id=? WHERE id=?")->execute(($this->Isotope->Config->orderPrefix . $orderId), $orderId);
		}
		else
		{
			$this->Database->prepare("UPDATE tl_iso_orders %s WHERE id=".$objOrder->id)
						   ->set($arrSet)
						   ->executeUncached();
						   
			$orderId = $objOrder->id;
		}
		
		// HOOK: process checkout 
		if (isset($GLOBALS['TL_HOOKS']['iso_writeOrder']) && is_array($GLOBALS['TL_HOOKS']['iso_writeOrder']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_writeOrder'] as $callback)
			{
				$this->import($callback[0]);
				$blnCheckout = $this->$callback[0]->$callback[1]($orderId, $blnCheckout, $this);
			}
		}
		
		if ($blnCheckout)
		{
			$salesEmail = $this->iso_sales_email ? $this->iso_sales_email : $GLOBALS['TL_ADMIN_EMAIL'];
			$customerMail = strlen($this->Isotope->Cart->billingAddress['email']) ? $this->Isotope->Cart->billingAddress['email'] : (strlen($this->Isotope->Cart->shippingAddress['email']) ? $this->Isotope->Cart->shippingAddress['email'] : (FE_USER_LOGGED_IN ? $this->User->email : ''));

			$arrData = array_merge($this->arrOrderData, array
			(
				'order_id'					=> ($this->Isotope->Config->orderPrefix . $orderId),
				'customer_name'				=> ($this->Isotope->Cart->billingAddress['firstname'] . ' ' . $this->Isotope->Cart->billingAddress['lastname']),
				'customer_email'			=> $customerMail,
				'items'						=> $this->Isotope->Cart->items,
				'products'					=> $this->Isotope->Cart->products,
				'subTotal'					=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal, false),
				'taxTotal'					=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->taxTotal, false),
				'shippingPrice'				=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->Shipping->price, false),
				'paymentPrice'				=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->Payment->price, false),
				'grandTotal'				=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal, false),
				'cart_text'					=> $this->replaceInsertTags($this->Isotope->Cart->getProducts('iso_products_text')),
				'cart_html'					=> $this->replaceInsertTags($this->Isotope->Cart->getProducts('iso_products_html')),
			));
			
			foreach( $this->Isotope->Cart->billingAddress as $k => $v )
			{
				$arrData['billing_'.$k] = $this->Isotope->formatValue('tl_iso_addresses', $k, $v);
			}
			
			foreach( $this->Isotope->Cart->shippingAddress as $k => $v )
			{
				$arrData['shipping_'.$k] = $this->Isotope->formatValue('tl_iso_addresses', $k, $v);
			}
			
			$this->log('New order ID ' . $orderId . ' has been placed', 'ModuleIsotopeCheckout writeOrder()', TL_ACCESS);
			
			if ($this->iso_mail_admin && strlen($salesEmail))
			{
				$this->Isotope->sendMail($this->iso_mail_admin, $salesEmail, $GLOBALS['TL_LANGUAGE'], $arrData);
			}
			
			if ($this->iso_mail_customer && strlen($customerMail))
			{
				$this->Isotope->sendMail($this->iso_mail_customer, $customerMail, $GLOBALS['TL_LANGUAGE'], $arrData);
			}
			else
			{
				$this->log('Unable to send customer confirmation for order ID '.$orderId, 'ModuleIsotopeCheckout writeOrder', TL_ERROR);
			}
			
			$objOrder = new IsotopeOrder();
			
			if ($objOrder->findBy('id', $orderId))
			{
				$objOrder->transferFromCollection($this->Isotope->Cart);
				$this->Isotope->Cart->delete();
			}
			
			unset($_SESSION['CHECKOUT_DATA']);
			unset($_SESSION['ISOTOPE']);
			
			// Store address in address book
			if ($this->iso_addToAddressbook && FE_USER_LOGGED_IN)
			{
				foreach( array('billing', 'shipping') as $address )
				{
					if ($arrData[$address.'_id'] == 0)
					{
						$arrAddress = array('pid'=>$this->User->id, 'tstamp'=>$time);
						
						foreach( $this->Isotope->Config->{$address.'_fields'} as $field )
						{
							if ($field['enabled'])
							{
								$arrAddress[$field['value']] = $arrData[$address.'_'.$field['value']];
							}
						}
						
						$this->Database->prepare("INSERT INTO tl_iso_addresses %s")->set($arrAddress)->execute();
					}
				}
			}
			
			return $strUniqueId;
		}
		
		return false;
	}
	
		
	protected function generateAddressWidget($field)
	{
		$strBuffer = '';
		$arrOptions = array();
		$arrCountries = ($field == 'billing_address' ? $this->Isotope->Config->billing_countries : $this->Isotope->Config->shipping_countries);
		
		if (FE_USER_LOGGED_IN)
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE pid=? ORDER BY isDefaultBilling DESC, isDefaultShipping DESC")->execute($this->User->id);
			
			while( $objAddress->next() )
			{
				if (!in_array($objAddress->country, $arrCountries))
					continue;
					
				$arrOptions[] = array
				(
					'value'		=> $objAddress->id,
					'label'		=> $this->Isotope->generateAddressString($objAddress->row(), ($field == 'billing_address' ? $this->Isotope->Config->billing_fields : $this->Isotope->Config->shipping_fields)),
				);
			}
		}
		
		switch($field)
		{
			case 'shipping_address':
				$arrAddress = $_SESSION['CHECKOUT_DATA'][$field] ? $_SESSION['CHECKOUT_DATA'][$field] : $this->Isotope->Cart->shippingAddress;				
				$intDefaultValue = strlen($arrAddress['id']) ? $arrAddress['id'] : -1;

				array_insert($arrOptions, 0, array(array
				(
					'value'	=> -1,
					'label' => ($this->Isotope->Cart->requiresPayment ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress']),
				)));
				
				$arrOptions[] = array
				(
					'value'	=> 0,
					'label' => $GLOBALS['TL_LANG']['MSC']['differentShippingAddress'],
				);
				break;
				
			case 'billing_address':
			default:
				$arrAddress = $_SESSION['CHECKOUT_DATA'][$field] ? $_SESSION['CHECKOUT_DATA'][$field] : $this->Isotope->Cart->billingAddress;
				$intDefaultValue = strlen($arrAddress['id']) ? $arrAddress['id'] : 0;
				
				if (FE_USER_LOGGED_IN)
				{
					$arrOptions[] = array
					(
						'value'	=> 0,
						'label' => &$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'],
					);
				}
				break;
		}
	
		// HOOK: add custom addresses, such as from a stored gift registry ******** ADDED BY BLAIR
		if (isset($GLOBALS['TL_HOOKS']['addCustomAddress']) && is_array($GLOBALS['TL_HOOKS']['addCustomAddress']))
		{
			foreach ($GLOBALS['TL_HOOKS']['addCustomAddress'] as $callback)
			{
				$this->import($callback[0]);
				$arrOptions = $this->$callback[0]->$callback[1]($arrOptions, $field, $this);
			}
		}
	
		if (count($arrOptions))
		{
			$strClass = $GLOBALS['TL_FFL']['radio'];
			
			$arrData = array('id'=>$field, 'name'=>$field, 'mandatory'=>true);
			
			$objWidget = new $strClass($arrData);
			$objWidget->options = $arrOptions;
			$objWidget->value = $intDefaultValue;
			$objWidget->onclick = "Isotope.toggleAddressFields(this, '" . $field . "_new');";
			$objWidget->storeValues = true;
			$objWidget->tableless = true;

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
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
			elseif ($objWidget->value != '')
			{
				$this->Input->setPost($objWidget->name, $objWidget->value);
				
				$objValidator = clone $objWidget;
				$objValidator->validate();
				
				if ($objValidator->hasErrors())
				{
					$this->doNotSubmit = true;
				}
			}
			
			$strBuffer .= $objWidget->parse();
		}
		
		if (strlen($_SESSION['CHECKOUT_DATA'][$field]['id']))
		{
			$this->Isotope->Cart->$field = $_SESSION['CHECKOUT_DATA'][$field]['id'];
		}
		elseif (!FE_USER_LOGGED_IN)
		{
			
		//	$this->doNotSubmit = true;
		}
		
		
		$strBuffer .= '<div id="' . $field . '_new" class="address_new"' . (((!FE_USER_LOGGED_IN && $field == 'billing_address') || $objWidget->value == 0) ? '>' : ' style="display:none">');
		$strBuffer .= '<span>' . $this->generateAddressWidgets($field) . '</span>';
		$strBuffer .= '</div>';
		
		return $strBuffer;
	}
	
	
	/**
	 * Generate the current step widgets.
	 * strResourceTable is used either to load a DCA or else to gather settings related to a given DCA.
	 */
	//!@todo <table...> was in a template, but I don't get why we need to define the table here?
	protected function generateAddressWidgets($strAddressType)
	{
		$strBuffer = '';
		
		$this->loadLanguageFile('tl_iso_addresses');
		$this->loadDataContainer('tl_iso_addresses');
		
		$arrFields = ($strAddressType == 'billing_address' ? $this->Isotope->Config->billing_fields : $this->Isotope->Config->shipping_fields);
		$arrDefault = $this->Isotope->Cart->$strAddressType;
		
		if ($arrDefault['id'] == -1)
			$arrDefault = array();
		
		foreach( $arrFields as $field )
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_addresses']['fields'][$field['value']];
			
			if (!is_array($arrData) || !$arrData['eval']['feEditable'] || !$field['enabled'] || ($arrData['eval']['membersOnly'] && !FE_USER_LOGGED_IN))
				continue;
			
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
			
			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
				continue;
			
			// Special field "country"
			if ($field['value'] == 'country')
			{
				$arrCountries = ($strAddressType == 'billing_address' ? $this->Isotope->Config->billing_countries : $this->Isotope->Config->shipping_countries);
				
				$arrData['options'] = array_values(array_intersect($arrData['options'], $arrCountries));
				$arrData['default'] = $this->Isotope->Config->country;
			}
			
			// Special field type "conditionalselect"
			if (strlen($arrData['eval']['conditionField']))
			{
				$arrData['eval']['conditionField'] = $strAddressType . '_' . $arrData['eval']['conditionField'];
			}
			
			$objWidget = new $strClass($this->prepareForWidget($arrData, $strAddressType . '_' . $field['value'], (strlen($_SESSION['CHECKOUT_DATA'][$strAddressType][$field['value']]) ? $_SESSION['CHECKOUT_DATA'][$strAddressType][$field['value']] : $arrDefault[$field['value']])));
			
			$objWidget->mandatory = $field['mandatory'] ? true : false;
			$objWidget->required = $objWidget->mandatory;
			$objWidget->label = $field['label'] ? $this->Isotope->translate($field['label']) : $objWidget->label;
			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
			
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && ($this->Input->post($strAddressType) === '0' || $this->Input->post($strAddressType) == ''))
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
					$arrAddress[$field['value']] = $varValue;
				}
			}
			elseif ($this->Input->post($strAddressType) === '0' || $this->Input->post($strAddressType) == '')
			{
				$this->Input->setPost($objWidget->name, $objWidget->value);
				
				$objValidator = clone $objWidget;
				$objValidator->validate();
				
				if ($objValidator->hasErrors())
				{
					$this->doNotSubmit = true;
				}
			}

			$strBuffer .= $objWidget->parse();
		}
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit && is_array($arrAddress) && count($arrAddress))
		{
			$arrAddress['id'] = 0;
			$_SESSION['CHECKOUT_DATA'][$strAddressType] = $arrAddress;		
		}

		if (is_array($_SESSION['CHECKOUT_DATA'][$strAddressType]) && $_SESSION['CHECKOUT_DATA'][$strAddressType]['id'] === 0)
		{
			$this->Isotope->Cart->$strAddressType = $_SESSION['CHECKOUT_DATA'][$strAddressType];
		}
		
		return '<table cellspacing="0" cellpadding="0" summary="Form fields">
' . $strBuffer . '
</table>';
	}
	
	
	protected function getCheckoutInfo()
	{
		if (!is_array($this->arrCheckoutInfo))
		{
			// Run trough all steps to collect checkout information
			$arrCheckoutInfo = array();
			foreach( $GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks )
			{
				foreach( $arrCallbacks as $callback )
				{
					if ($callback[0] == 'ModuleIsotopeCheckout')
					{
						$arrInfo = $this->{$callback[1]}(true);
					}
					else
					{
						$this->import($callback[0]);
						$arrInfo = $this->{$callback[0]}->{$callback[1]}($this, true);
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
	
	
	//!@todo implement google analytics using Yoans module
	public function googleTracking()
	{
		if(!$this->Isotope->Config->enableGoogleAnalytics || $this->Input->get('step')!='complete' || !file_exists(TL_ROOT . '/system/modules/googleanalytics/GoogleAnalytics.php'))
		{
			return '';
		}
		
		$objTemplate = new IsotopeTemplate('iso_google_analytics');
		
		$arrState = explode(',', $this->Isotope->Cart->billingAddress['subdivision']);
		
		$arrProducts = $this->Isotope->Cart->getProducts();
		
		$arrVariantValues = $this->getProductVariantValues($arrProducts);
			
		foreach($arrProducts as $objProduct)
		{
			$strVariant = (is_array($arrVariantValues[$objProduct->id]['variants']) ? implode(' ', $arrVariantValues[$objProduct->id]['variants']) : ''); 
			
			$arrItems[] = array
			(
				'sku'		=> $objProduct->sku,
				'name'		=> $objProduct->name,
				'variant'	=> $strVariant,
				'price'		=> $objProduct->price,
				'quantity'	=> $objProduct->quantity_requested
			);
		}
		
		$objTemplate->id = $intOrderId;
		$objTemplate->storeName = $this->Isotope->Config->name;
		$objTemplate->grandTotal = $this->Isotope->Cart->grandTotal;
		$objTemplate->tax = $this->Isotope->Cart->taxTotal;
		$objTemplate->shipping = $this->Isotope->Cart->shippingTotal;
		$objTemplate->city = $this->Isotope->Cart->billingAddress['city'];
		$objTemplate->state = $arrState[1];
		$objTemplate->country = $this->Isotope->Cart->billingAddress['country'];
		$objTemplate->items = $arrItems;
	
		return $objTemplate->parse();
	}
	
	
	private function getProductVariantValue($arrProducts)
	{
		$objVariantAttributes = $this->Database->prepare("SELECT name, field_name FROM tl_iso_attributes WHERE variant_option=?")
									  				->execute(1);
									  				
		if(!$objVariantAttributes->numRows)
		{
			return '';
		}
		
		while($objVariantAttributes->next())
		{
			$strField = $objVariantAttributes->field_name;
			
			foreach($arrProducts as $objProduct)
			{
				if(property_exists($objProduct, $strField))
				{
					if($row[$strField])
					{
						$arrReturn[$objProduct->id]['variants'][] = $row[$strField];
					}
				}
			}
		}
		
		return $arrReturn;
	}
}

