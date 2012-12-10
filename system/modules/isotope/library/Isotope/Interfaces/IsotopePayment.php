<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;


/**
 * IsotopePayment interface describes an Isotope payment method
 */
interface IsotopePayment
{

    /**
	 * Process checkout payment. Must be implemented in each payment module
	 * @access public
	 * @return mixed
	 */
	public function processPayment();

	/**
	 * Process post-sale requests. Does nothing by default.
	 *
	 * This function can be called from the postsale.php file when the payment server is requestion/posting a status change.
	 * You can see an implementation example in PaymentPostfinance.php
	 */
	public function processPostSale();

	/**
	 * Return a html form for payment data or an empty string.
	 *
	 * The input fields should be from array "payment" including the payment module ID.
	 * Example: <input type="text" name="payment[$this->id][cc_num]" />
	 * Post-Value "payment" is automatically stored in $_SESSION['CHECKOUT_DATA']['payment']
	 * You can set $objCheckoutModule->doNotSubmit = true if post is sent but data is invalid.
	 *
	 * @param object The checkout module object.
	 * @return string
	 */
	public function paymentForm($objCheckoutModule);

	/**
	 * Return a html form for checkout or false
	 * @return mixed
	 */
	public function checkoutForm();

	/**
	 * Return information or advanced features in the backend.
	 *
	 * Use this function to present advanced features or basic payment information for an order in the backend.
	 * @param integer Order ID
	 * @return string
	 */
	public function backendInterface($orderId);

	/**
	 * Return the checkout review information.
	 *
	 * Use this to return custom checkout information about this payment module.
	 * Example: parial information about the used credit card.
	 *
	 * @return string
	 */
	public function checkoutReview();

	/**
	 * Get the checkout surcharge for this shipping method
	 */
	public function getSurcharge($objCollection);
}
