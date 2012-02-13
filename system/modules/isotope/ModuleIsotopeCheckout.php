<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@certo-net.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ModuleIsotopeCheckout
 * Front end module Isotope "checkout".
 */
class ModuleIsotopeCheckout extends ModuleIsotope
{

	/**
	 * Order data. Each checkout step can provide key-value (string) data for the order email.
	 * @var array
	 */
	public $arrOrderData = array();

	/**
	 * Do not submit form
	 * @var boolean
	 */
	public $doNotSubmit = false;
	
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_checkout';

	/**
	 * Disable caching of the frontend page if this module is in use.
	 * @var boolean
	 */
	protected $blnDisableCache = true;

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
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->strCurrentStep = $this->Input->get('step');
		return parent::generate();
	}
	
	
	/**
	 * Returns the current form ID
	 * @return string
	 */
	public function getFormId()
	{
		return $this->strFormId;
	}


	/**
	 * Generate module
	 * @return void
	 */
	protected function compile()
	{
		// Order has been completed (postsale request)
		if ($this->strCurrentStep == 'complete' && $this->Input->get('uid') != '')
		{
			$objOrder = new IsotopeOrder();

			if ($objOrder->findBy('uniqid', $this->Input->get('uid')) && $objOrder->checkout_complete)
			{
				$this->redirect($this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->orderCompleteJumpTo)->fetchAssoc()) . '?uid='.$objOrder->uniqid);
			}
		}

		// Return error message if cart is empty
		if (!$this->Isotope->Cart->items)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
			return;
		}

		// Insufficient cart subtotal
		if ($this->Isotope->Config->cartMinSubtotal > 0 && $this->Isotope->Config->cartMinSubtotal > $this->Isotope->Cart->subTotal)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'error';
			$this->Template->message = sprintf($GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'], $this->Isotope->formatPriceWithCurrency($this->Isotope->Config->cartMinSubtotal));
			return;
		}

		// Redirect to login page if not logged in
		if ($this->iso_checkout_method == 'member' && !FE_USER_LOGGED_IN)
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
		elseif ($this->iso_checkout_method == 'guest' && FE_USER_LOGGED_IN)
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
		foreach ($GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks)
		{
			// Step could be removed while looping
			if (!isset($GLOBALS['ISO_CHECKOUT_STEPS'][$step]))
			{
				continue;
			}

			$this->strFormId = 'iso_mod_checkout_' . $step;
			$this->Template->formId = $this->strFormId;
			$this->Template->formSubmit = $this->strFormId;
			$strBuffer = '';

			foreach ($arrCallbacks as $callback)
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

				// the user wanted to proceed but the current step is not completed yet
				if ($this->doNotSubmit && $step != $this->strCurrentStep)
				{
					$this->redirect($this->addToUrl('step=' . $step, true));
				}
			}

			if ($step == $this->strCurrentStep)
			{
				break;
			}
		}

		if ($this->strCurrentStep == 'process')
		{
			$this->writeOrder();
			$strBuffer = $this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->checkoutForm() : false;

			if ($strBuffer === false)
			{
				$this->redirect($this->addToUrl('step=complete', true));
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

				$objOrder = new IsotopeOrder();

				if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id) || !$objOrder->checkout($this->Isotope->Cart))
				{
					$this->redirect($this->addToUrl('step=failed', true));
				}

				unset($_SESSION['CHECKOUT_DATA']);
				unset($_SESSION['ISOTOPE']);

				$this->redirect($this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->orderCompleteJumpTo)->fetchAssoc()) . '?uid='.$objOrder->uniqid);
			}
			elseif ($strBuffer === false)
			{
				$this->redirect($this->addToUrl('step=failed', true));
			}
			else
			{
				$this->Template->showNext = false;
				$this->Template->showPrevious = false;
			}
		}

		$this->Template->fields = $strBuffer;

		if (!strlen($this->strCurrentStep))
		{
			$this->strCurrentStep = $step;
		}

		// Show checkout steps
		$arrStepKeys = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);
		$blnPassed = true;
		$total = count($arrStepKeys) - 1;
		$arrSteps = array();
		
		if ($this->strCurrentStep != 'process' && $this->strCurrentStep != 'complete')
		{
			foreach ($arrStepKeys as $i => $step)
			{
				if ($this->strCurrentStep == $step)
				{
					$blnPassed = false;
				}
	
				$blnActive = $this->strCurrentStep == $step ? true : false;
	
				$arrSteps[] = array
				(
					'isActive'	=> $blnActive,
					'class'		=> 'step_' . $i . (($i == 0) ? ' first' : '') . ($i == $total ? ' last' : '') . ($blnActive ? ' active' : '') . ($blnPassed ? ' passed' : '') . ((!$blnPassed && !$blnActive) ? ' upcoming' : '') . ' '. $step,
					'label'		=> (strlen($GLOBALS['TL_LANG']['ISO']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['ISO']['checkout_' . $step] : $step),
					'href'		=> ($blnPassed ? $this->addToUrl('step=' . $step, true) : ''),
					'title'		=> specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['checkboutStepBack'], (strlen($GLOBALS['TL_LANG']['ISO']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['ISO']['checkout_' . $step] : $step))),
				);
			}
		}
		
		$this->Template->steps = $arrSteps;
		$this->Template->activeStep = $GLOBALS['ISO_LANG']['MSC']['activeStep'];

		// Hide back buttons it this is the first step
		if (array_search($this->strCurrentStep, $arrStepKeys) === 0)
		{
			$this->Template->showPrevious = false;
		}

		// Show "confirm order" button if this is the last step
		elseif (array_search($this->strCurrentStep, $arrStepKeys) === $total)
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
	 * @return void
	 */
	protected function redirectToNextStep()
	{
		$arrSteps = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);

		if (!in_array($this->strCurrentStep, $arrSteps))
		{
			$this->redirect($this->addToUrl('step='.array_shift($arrSteps), true));
		}
		else
		{
			// key of the next step
			$intKey = array_search($this->strCurrentStep, $arrSteps) + 1;

			// redirect to step "process" if the next step is the last one
			if ($intKey == count($arrSteps))
			{
				$this->redirect($this->addToUrl('step=process', true));
			}
			else
			{
				$this->redirect($this->addToUrl('step='.$arrSteps[$intKey], true));
			}
		}
	}


	/**
	 * Redirect visotor to the previous step in ISO_CHECKOUT_STEPS
	 * @return void
	 */
	protected function redirectToPreviousStep()
	{
		$arrSteps = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);

		if (!in_array($this->strCurrentStep, $arrSteps))
		{
			$this->redirect($this->addToUrl('step='.array_shift($arrSteps), true));
		}
		else
		{
			$strKey = array_search($this->strCurrentStep, $arrSteps);
			$this->redirect($this->addToUrl('step='.$arrSteps[($strKey-1)], true));
		}
	}


	/**
	 * Generate billing address interface and return it as HTML string
	 * @param boolean
	 * @return string
	 */
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
					'edit'		=> $this->addToUrl('step=address', true),
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
			$this->arrOrderData['billing_address_text']	= strip_tags(str_replace(array('<br />', '<br>'), "\n", $strBillingAddress));
		}

		return $objTemplate->parse();
	}


	/**
	 * Generate shipping address interface and return it as HTML string
	 * @param boolean
	 * @return string
	 */
	protected function getShippingAddressInterface($blnReview=false)
	{
		if (!$this->Isotope->Cart->requiresShipping)
		{
			return '';
		}

		if ($blnReview)
		{
			if ($this->Isotope->Cart->shippingAddress['id'] == -1)
			{
				return false;
			}

			return array
			(
				'shipping_address' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['shipping_address'],
					'info'		=> $this->Isotope->generateAddressString($this->Isotope->Cart->shippingAddress, $this->Isotope->Config->shipping_fields),
					'edit'		=> $this->addToUrl('step=address', true),
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


	/**
	 * Generate shipping modules interface and return it as HTML string
	 * @param boolean
	 * @return string
	 */
	protected function getShippingModulesInterface($blnReview=false)
	{
		if ($blnReview)
		{
			if (!$this->Isotope->Cart->hasShipping)
			{
				return false;
			}

			return array
			(
				'shipping_method' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['shipping_method'],
					'info'		=> $this->Isotope->Cart->Shipping->checkoutReview(),
					'note'		=> $this->Isotope->Cart->Shipping->note,
					'edit'		=> $this->addToUrl('step=shipping', true),
				),
			);
		}

		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_shipping_modules);

		if (is_array($arrModuleIds) && count($arrModuleIds))
		{
			$arrData = $this->Input->post('shipping');
			$arrModuleIds = array_map('intval', $arrModuleIds);
			
			$objModules = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id IN (" . implode(',', $arrModuleIds) . ")" . (BE_USER_LOGGED_IN ? '' : " AND enabled='1'") . " ORDER BY " . $this->Database->findInSet('id', $arrModuleIds));

			while ($objModules->next())
			{
				$strClass = $GLOBALS['ISO_SHIP'][$objModules->type];

				if (!strlen($strClass) || !$this->classFileExists($strClass))
				{
					continue;
				}

				$objModule = new $strClass($objModules->row());

				if (!$objModule->available)
				{
					continue;
				}

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

	 			$arrModules[] = array
	 			(
	 				'id'		=> $objModule->id,
	 				'label'		=> $objModule->label,
	 				'price'		=> $strPrice,
	 				'checked'	=> (($this->Isotope->Cart->Shipping->id == $objModule->id || $objModules->numRows == 1) ? ' checked="checked"' : ''),
	 				'note'		=> $objModule->note,
	 				'form'		=> $objModule->getShippingOptions($this),
	 			);

	 			$objLastModule = $objModule;
			}
		}

		$objTemplate = new IsotopeTemplate('iso_checkout_shipping_method');

		if (!count($arrModules))
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
			if ($this->Input->post('FORM_SUBMIT') != '')
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


	/**
	 * Generate payment modules interface and return it as HTML string
	 * @param boolean
	 * @return string
	 */
	protected function getPaymentModulesInterface($blnReview=false)
	{
		if ($blnReview)
		{
			if (!$this->Isotope->Cart->hasPayment)
			{
				return false;
			}

			return array
			(
				'payment_method' => array
				(
					'headline'	=> $GLOBALS['TL_LANG']['ISO']['payment_method'],
					'info'		=> $this->Isotope->Cart->Payment->checkoutReview(),
					'note'		=> $this->Isotope->Cart->Payment->note,
					'edit'		=> $this->addToUrl('step=payment', true),
				),
			);
		}

		$arrModules = array();
		$arrModuleIds = deserialize($this->iso_payment_modules);

		if (is_array($arrModuleIds) && !empty($arrModuleIds))
		{
			$arrData = $this->Input->post('payment');
			$arrModuleIds = array_map('intval', $arrModuleIds);
			
			$objModules = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE id IN (" . implode(',', $arrModuleIds) . ")" . (BE_USER_LOGGED_IN ? '' : " AND enabled='1'") . " ORDER BY " . $this->Database->findInSet('id', $arrModuleIds));

			while ($objModules->next())
			{
				$strClass = $GLOBALS['ISO_PAY'][$objModules->type];

				if (!strlen($strClass) || !$this->classFileExists($strClass))
				{
					continue;
				}

				$objModule = new $strClass($objModules->row());

				if (!$objModule->available)
				{
					continue;
				}

				if (is_array($arrData) && $arrData['module'] == $objModule->id)
	 			{
	 				$_SESSION['CHECKOUT_DATA']['payment'] = $arrData;
	 			}

	 			if (is_array($_SESSION['CHECKOUT_DATA']['payment']) && $_SESSION['CHECKOUT_DATA']['payment']['module'] == $objModule->id)
	 			{
	 				$this->Isotope->Cart->Payment = $objModule;
	 			}

	 			$fltPrice = $objModule->price;
	 			$strSurcharge = $objModule->surcharge;
	 			$strPrice = ($fltPrice != 0) ? (($strSurcharge == '' ? '' : ' ('.$strSurcharge.')') . ': '.$this->Isotope->formatPriceWithCurrency($fltPrice)) : '';

	 			$arrModules[] = array
	 			(
	 				'id'		=> $objModule->id,
	 				'label'		=> $objModule->label,
	 				'price'		=> $strPrice,
	 				'checked'	=> (($this->Isotope->Cart->Payment->id == $objModule->id || $objModules->numRows == 1) ? ' checked="checked"' : ''),
	 				'note'		=> $objModule->note,
	 				'form'		=> $objModule->paymentForm($this),
	 			);

	 			$objLastModule = $objModule;
			}
		}

		$objTemplate = new IsotopeTemplate('iso_checkout_payment_method');

		if (!count($arrModules))
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
			if ($this->Input->post('FORM_SUBMIT') != '')
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


	/**
	 * Generate order conditions interface and return it as HTML string
	 * @param boolean
	 * @return string
	 */
	protected function getOrderConditionsInterface($blnReview=false)
	{
		if (!$this->iso_order_conditions)
		{
			return '';
		}

		if ($blnReview)
		{
			if (!$this->doNotSubmit)
			{
				if (is_array($_SESSION['FORM_DATA']))
				{
					foreach( $_SESSION['FORM_DATA'] as $name => $value )
					{
						$this->arrOrderData['form_' . $name] = $value;
					}
				}

				if (is_array($_SESSION['FILES']))
				{
					foreach( $_SESSION['FILES'] as $name => $file )
					{
						$this->arrOrderData['form_' . $name] = $this->Environment->base . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
					}
				}
			}

			return '';
		}

		$this->import('IsotopeFrontend');
		$objForm = $this->IsotopeFrontend->prepareForm($this->iso_order_conditions, $this->strFormId, array('tableless'=>$this->tableless));

		// Form not found
		if ($objForm == null)
		{
			return '';
		}

		$this->doNotSubmit = $objForm->blnHasErrors ? true : $this->doNotSubmit;
		$this->Template->enctype = $objForm->enctype;

		if (!$this->doNotSubmit)
		{
			foreach ($objForm->arrFormData as $name => $value)
			{
				$this->arrOrderData['form_' . $name] = $value;
			}

			foreach ($objForm->arrFiles as $name => $file)
			{
				$this->arrOrderData['form_' . $name] = $this->Environment->base . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
			}
		}

		$objTemplate = new IsotopeTemplate('iso_checkout_order_conditions');
		$objTemplate->attributes	= $objForm->attributes;
		$objTemplate->tableless		= $this->tableless;

		$parse = create_function('$a', 'return $a->parse();');
		$objTemplate->hidden = implode('', array_map($parse, $objForm->arrHidden));
		$objTemplate->fields = implode('', array_map($parse, $objForm->arrFields));

		return $objTemplate->parse();
	}


	/**
	 * Generate order review interface and return it as HTML string
	 * @param boolean
	 * @return string
	 */
	protected function getOrderReviewInterface($blnReview=false)
	{
		if ($blnReview)
		{
			return;
		}

		$objTemplate = new IsotopeTemplate('iso_checkout_order_review');
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['order_review'];
		$objTemplate->message = $GLOBALS['TL_LANG']['ISO']['order_review_message'];
		$objTemplate->summary = $GLOBALS['ISO_LANG']['MSC']['cartSummary'];

		// Surcharges must be initialized before getProducts() to apply tax_id to each product
		$arrSurcharges = $this->Isotope->Cart->getSurcharges();
		$arrProductData = array();
		$arrProducts = $this->Isotope->Cart->getProducts();

		foreach ($arrProducts as $objProduct)
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
		$objTemplate->products = IsotopeFrontend::generateRowClass($arrProductData, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
		$objTemplate->surcharges = IsotopeFrontend::formatSurcharges($arrSurcharges);
		$objTemplate->edit_info = $GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo'];
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];

		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal);
		$objTemplate->grandTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal);

		return $objTemplate->parse();
	}


	/**
	 * Save the order
	 * @return void
	 */
	protected function writeOrder()
	{
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$objOrder->uniqid		= uniqid($this->Isotope->Config->orderPrefix, true);
			$objOrder->cart_id		= $this->Isotope->Cart->id;
			$objOrder->findBy('id', $objOrder->save());
		}

		$objOrder->pid				= (FE_USER_LOGGED_IN ? $this->User->id : 0);
		$objOrder->date				= time();
		$objOrder->config_id		= (int) $this->Isotope->Config->id;
		$objOrder->shipping_id		= ($this->Isotope->Cart->hasShipping ? $this->Isotope->Cart->Shipping->id : 0);
		$objOrder->payment_id		= ($this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->id : 0);
		$objOrder->subTotal			= $this->Isotope->Cart->subTotal;
		$objOrder->taxTotal			= $this->Isotope->Cart->taxTotal;
		$objOrder->shippingTotal	= $this->Isotope->Cart->shippingTotal;
		$objOrder->grandTotal		= $this->Isotope->Cart->grandTotal;
		$objOrder->surcharges		= $this->Isotope->Cart->getSurcharges();
		$objOrder->checkout_info	= $this->getCheckoutInfo();
		$objOrder->status			= '';
		$objOrder->language			= $GLOBALS['TL_LANGUAGE'];
		$objOrder->billing_address	= $this->Isotope->Cart->billingAddress;
		$objOrder->shipping_address	= $this->Isotope->Cart->shippingAddress;
		$objOrder->currency			= $this->Isotope->Config->currency;
		$objOrder->iso_sales_email		= $this->iso_sales_email ? $this->iso_sales_email : (($GLOBALS['TL_ADMIN_NAME'] != '') ? sprintf('%s <%s>', $GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) : $GLOBALS['TL_ADMIN_EMAIL']);
		$objOrder->iso_mail_admin		= $this->iso_mail_admin;
		$objOrder->iso_mail_customer	= $this->iso_mail_customer;
		$objOrder->iso_addToAddressbook	= $this->iso_addToAddressbook;
		$objOrder->new_order_status		= ($this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->new_order_status : 'pending');

		$strCustomerName = '';
		$strCustomerEmail = '';

		if ($this->Isotope->Cart->billingAddress['email'] != '')
		{
			$strCustomerName = $this->Isotope->Cart->billingAddress['firstname'] . ' ' . $this->Isotope->Cart->billingAddress['lastname'];
			$strCustomerEmail = $this->Isotope->Cart->billingAddress['email'];
		}
		elseif ($this->Isotope->Cart->shippingAddress['email'] != '')
		{
			$strCustomerName = $this->Isotope->Cart->shippingAddress['firstname'] . ' ' . $this->Isotope->Cart->shippingAddress['lastname'];
			$strCustomerEmail = $this->Isotope->Cart->shippingAddress['email'];
		}
		elseif (FE_USER_LOGGED_IN && $this->User->email != '')
		{
			$strCustomerName = $this->User->firstname . ' ' . $this->User->lastname;
			$strCustomerEmail = $this->User->email;
		}

		if (trim($strCustomerName) != '')
		{
			$strCustomerEmail = sprintf('"%s" <%s>', IsotopeEmail::romanizeFriendlyName($strCustomerName), $strCustomerEmail);
		}

		$objOrder->iso_customer_email	= $strCustomerEmail;

		$arrData = array_merge($this->arrOrderData, array
		(
			'uniqid'			=> $objOrder->uniqid,
			'items'				=> $this->Isotope->Cart->items,
			'products'			=> $this->Isotope->Cart->products,
			'subTotal'			=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal, false),
			'taxTotal'			=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->taxTotal, false),
			'shippingPrice'		=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->Shipping->price, false),
			'paymentPrice'		=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->Payment->price, false),
			'grandTotal'		=> $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal, false),
			'cart_text'			=> strip_tags($this->replaceInsertTags($this->Isotope->Cart->getProducts('iso_products_text'))),
			'cart_html'			=> $this->replaceInsertTags($this->Isotope->Cart->getProducts('iso_products_html')),
		));

		$objOrder->email_data = $arrData;
		$objOrder->save();
	}


	/**
	 * Generate address widget and return it as HTML string
	 * @param string
	 * @return string
	 */
	protected function generateAddressWidget($field)
	{
		$strBuffer = '';
		$arrOptions = array();
		$arrCountries = ($field == 'billing_address' ? $this->Isotope->Config->billing_countries : $this->Isotope->Config->shipping_countries);

		if (FE_USER_LOGGED_IN)
		{
			$objAddress = $this->Database->execute("SELECT * FROM tl_iso_addresses WHERE pid={$this->User->id} AND store_id={$this->Isotope->Config->store_id} ORDER BY isDefaultBilling DESC, isDefaultShipping DESC");

			while ($objAddress->next())
			{
				if (is_array($arrCountries) && !in_array($objAddress->country, $arrCountries))
				{
					continue;
				}

				$arrOptions[] = array
				(
					'value'		=> $objAddress->id,
					'label'		=> $this->Isotope->generateAddressString($objAddress->row(), ($field == 'billing_address' ? $this->Isotope->Config->billing_fields : $this->Isotope->Config->shipping_fields)),
				);
			}
		}

		switch ($field)
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

		// HOOK: add custom addresses, such as from a stored gift registry
		if (isset($GLOBALS['ISO_HOOKS']['addCustomAddress']) && is_array($GLOBALS['ISO_HOOKS']['addCustomAddress']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['addCustomAddress'] as $callback)
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

			//$this->doNotSubmit = true;
		}

		$strBuffer .= '<div id="' . $field . '_new" class="address_new"' . (((!FE_USER_LOGGED_IN && $field == 'billing_address') || $objWidget->value == 0) ? '>' : ' style="display:none">');
		$strBuffer .= '<span>' . $this->generateAddressWidgets($field, count($arrOptions)) . '</span>';
		$strBuffer .= '</div>';
		return $strBuffer;
	}


	/**
	 * Generate the current step widgets.
	 * strResourceTable is used either to load a DCA or else to gather settings related to a given DCA.
	 *
	 * @todo <table...> was in a template, but I don't get why we need to define the table here?
	 * @param string
	 * @param integer
	 * @return string
	 */
	protected function generateAddressWidgets($strAddressType, $intOptions)
	{
		$arrWidgets = array();

		$this->loadLanguageFile('tl_iso_addresses');
		$this->loadDataContainer('tl_iso_addresses');

		$arrFields = ($strAddressType == 'billing_address' ? $this->Isotope->Config->billing_fields : $this->Isotope->Config->shipping_fields);
		$arrDefault = $this->Isotope->Cart->$strAddressType;

		if ($arrDefault['id'] == -1)
		{
			$arrDefault = array();
		}

		foreach ($arrFields as $field)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_addresses']['fields'][$field['value']];

			if (!is_array($arrData) || !$arrData['eval']['feEditable'] || !$field['enabled'] || ($arrData['eval']['membersOnly'] && !FE_USER_LOGGED_IN))
			{
				continue;
			}

			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			// Special field "country"
			if ($field['value'] == 'country')
			{
				$arrCountries = ($strAddressType == 'billing_address' ? $this->Isotope->Config->billing_countries : $this->Isotope->Config->shipping_countries);
				$arrData['options'] = array_values(array_intersect($arrData['options'], $arrCountries));
				$arrDefault['country'] = $this->Isotope->Config->country;
			}

			// Special field type "conditionalselect"
			elseif (strlen($arrData['eval']['conditionField']))
			{
				$arrData['eval']['conditionField'] = $strAddressType . '_' . $arrData['eval']['conditionField'];
			}

			// Special fields "isDefaultBilling" & "isDefaultShipping"
			elseif (($field['value'] == 'isDefaultBilling' && $strAddressType == 'billing_address' && $intOptions < 2) || ($field['value'] == 'isDefaultShipping' && $strAddressType == 'shipping_address' && $intOptions < 3))
			{
				$arrDefault[$field['value']] = '1';
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, $strAddressType . '_' . $field['value'], (strlen($_SESSION['CHECKOUT_DATA'][$strAddressType][$field['value']]) ? $_SESSION['CHECKOUT_DATA'][$strAddressType][$field['value']] : $arrDefault[$field['value']])));

			$objWidget->mandatory = $field['mandatory'] ? true : false;
			$objWidget->required = $objWidget->mandatory;
			$objWidget->tableless = $this->tableless;
			$objWidget->label = $field['label'] ? $this->Isotope->translate($field['label']) : $objWidget->label;
			$objWidget->storeValues = true;

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

			$arrWidgets[] = $objWidget;
		}
		
		$arrWidgets = IsotopeFrontend::generateRowClass($arrWidgets, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);

		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit && is_array($arrAddress) && count($arrAddress))
		{
			$arrAddress['id'] = 0;
			$_SESSION['CHECKOUT_DATA'][$strAddressType] = $arrAddress;
		}

		if (is_array($_SESSION['CHECKOUT_DATA'][$strAddressType]) && $_SESSION['CHECKOUT_DATA'][$strAddressType]['id'] === 0)
		{
			$this->Isotope->Cart->$strAddressType = $_SESSION['CHECKOUT_DATA'][$strAddressType];
		}
		
		$strBuffer = '';
		
		foreach ($arrWidgets as $objWidget)
		{
			$strBuffer .= $objWidget->parse();
		}

		if ($this->tableless)
		{
			return $strBuffer;
		}

		return '
<table>
' . $strBuffer . '
</table>';
	}


	/**
	 * Return the checkout information as array
	 * @return array
	 */
	protected function getCheckoutInfo()
	{
		if (!is_array($this->arrCheckoutInfo))
		{
			$arrCheckoutInfo = array();

			// Run trough all steps to collect checkout information
			foreach ($GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks)
			{
				foreach ($arrCallbacks as $callback)
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


	/**
	 * Override parent addToUrl function. Use generateFrontendUrl if we want to remove all parameters.
	 * @param string
	 * @param boolean
	 * @return string
	 */
	protected function addToUrl($strRequest, $blnIgnoreParams=false)
	{
		if ($blnIgnoreParams)
		{
			global $objPage;
			return $this->generateFrontendUrl($objPage->row(), '/' . str_replace(array('=', '&amp;', '&'), '/', $strRequest));
		}

		return parent::addToUrl($strRequest, $blnIgnoreParams);
	}


	/**
	 * Return product variant value as array
	 * @param array
	 * @return array
	 */
	private function getProductVariantValue($arrProducts)
	{
		$objVariantAttributes = $this->Database->prepare("SELECT name, field_name FROM tl_iso_attributes WHERE variant_option=?")->execute(1);

		if (!$objVariantAttributes->numRows)
		{
			return '';
		}

		while ($objVariantAttributes->next())
		{
			$strField = $objVariantAttributes->field_name;

			foreach ($arrProducts as $objProduct)
			{
				if (property_exists($objProduct, $strField))
				{
					if ($row[$strField])
					{
						$arrReturn[$objProduct->id]['variants'][] = $row[$strField];
					}
				}
			}
		}

		return $arrReturn;
	}
}

