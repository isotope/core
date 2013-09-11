<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Isotope payment method for www.worldpay.com
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class PaymentWorldpay extends IsotopePayment
{

	/**
	 * processPayment function.
	 *
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		$objOrder = new IsotopeOrder();
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			return false;
		}

		if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
		{
			IsotopeFrontend::clearTimeout();
			return true;
		}

		if (IsotopeFrontend::setTimeout())
		{
			// Do not index or cache the page
			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'processing';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
			return $objTemplate->parse();
		}

		$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
		$this->redirect($this->addToUrl('step=failed', true));
	}


	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale()
	{

	}


	/**
	 * Return the PayPal form.
	 *
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{

	}
}
