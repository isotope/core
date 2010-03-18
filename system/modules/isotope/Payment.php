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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

/**
 * Parent class for all payment gateway modules
 */
abstract class Payment extends Frontend
{

	/**
	 * Template
	 *
	 * @access protected
	 * @var string
	 */
	protected $strTemplate;

	/**
	 * Current record
	 *
	 * @access protected
	 * @var array
	 */
	protected $arrData = array();
	
	
	/**
	 * Initialize the object
	 *
	 * @access public
	 * @param array $arrRow
	 */
	public function __construct($arrRow)
	{
		parent::__construct();

		$arrRow['allowed_cc_types'] = deserialize($arrRow['allowed_cc_types']);
		if (is_array($arrRow['allowed_cc_types']))
		{
			$arrRow['allowed_cc_types'] = array_intersect($this->getAllowedCCTypes(), $arrRow['allowed_cc_types']);
		}
		
		$this->arrData = $arrRow;
	}
	
	
	/**
	 * Set an object property
	 *
	 * @access public
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}


	/**
	 * Return an object property
	 *
	 * @access public
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'available':
			
				if (!$this->enabled && !BE_USER_LOGGED_IN)
					return false;
				
				$this->import('IsotopeCart', 'Cart');
				
				if (($this->minimum_total > 0 && $this->minimum_total > $this->Cart->subTotal) || ($this->maximum_total > 0 && $this->maximum_total < $this->Cart->subTotal))
					return false;
					
				$arrCountries = deserialize($this->countries);
				if(is_array($arrCountries) && count($arrCountries) && !in_array($this->Cart->billingAddress['country'], $arrCountries))
					return false;
					
				$arrShippings = deserialize($this->shipping_modules);
				if (is_array($arrShippings) && count($arrShippings) && ((!$this->Cart->hasShipping && !in_array(0, $arrShippings)) || ($this->Cart->hasShipping && !in_array($this->Cart->Shipping->id, $arrShippings))))
					return false;
					
				$arrTypes = deserialize($this->product_types);
				if (is_array($arrTypes) && count($arrTypes))
				{
					$arrProducts = $this->Cart->getProducts();
					foreach( $arrProducts as $objProduct )
					{
						if (!in_array($objProduct->type, $arrTypes))
							return false;
					}
				}
					
				return true;
				break;
				
			case 'price':
				return $this->Isotope->calculatePrice($this->arrData['price'], $this->arrData['tax_class']);
				break;
		}
		
		return $this->arrData[$strKey];
	}
	
	
	/**
	 * Return a list of buttons for the table row in backend
	 * 
	 * @access public
	 * @return string
	 */
	public function moduleOperations()
	{
		return '';
	}
	
	
	/**
	 * Return a list of order status options.
	 *
	 * Allowed return values are:
	 * - pending
	 * - processing
	 * - shipped
	 * - complete
	 * - on_hold
	 * - cancelled
	 * 
	 * @access public
	 * @return array
	 */
	public function statusOptions()
	{
		return array('pending', 'processing');
	}

	
	/**
	 * Process checkout payment. Must be implemented in each payment module.
	 * 
	 * @abstract
	 * @access public
	 * @return bool
	 */
	abstract public function processPayment();
	
	
	/**
	 * Process post-sale requests. Does nothing by default.
	 *
	 * This function can be called from the postsale.php file when the payment server is requestion/posting a status change.
	 * You can see an implementation example in PaymentPostfinance.php
	 * 
	 * @access public
	 * @return void
	 */
	public function processPostSale() {}
	
	
	/**
	 * Return a html form for payment data or an empty string.
	 *
	 * The input fields should be from array "payment" including the payment module ID.
	 * Example: <input type="text" name="payment[$this->id][cc_num]" />
	 * Post-Value "payment" is automatically stored in $_SESSION['CHECKOUT_DATA']['payment']
	 * You can set $objCheckoutModule->doNotSubmit = true if post is sent but data is invalid.
	 * 
	 * @access	public
	 * @param	object	The checkout module object. 
	 * @return	string
	 */
	public function paymentForm($objCheckoutModule)
	{
		return '';
	}
	
	
	/**
	 * Return a html form for checkout or false.
	 * 
	 * @access public
	 * @return mixed
	 */
	public function checkoutForm()
	{
		return false;
	}
	
	
	/**
	 * Return a list of valid credit card types for this payment module
	 */
	public function getAllowedCCTypes()
	{
		return array();
	}
	
	
	/**
	 * Return the checkout review information.
	 *
	 * Use this to return custom checkout information about this payment module.
	 * Example: parial information about the used credit card.
	 *
	 * @access public
	 * @return string
	 */
	public function checkoutReview()
	{
		return $this->label;
	}
	
	
	/**
	 * Validate a credit card number and return the card type.
	 * http://regexlib.com/UserPatterns.aspx?authorid=7128ecda-5ab1-451d-98d9-f94d2a453b37
	 *
	 * @access	protected
	 * @param	string
	 * @return	mixed
	 */
	protected function validateCreditCard($strNumber)
	{
		$strNumber = preg_replace('@[^0-9]+@', '', $strNumber);
		
		if (preg_match('@(^4\d{12}$)|(^4[0-8]\d{14}$)|(^(49)[^013]\d{13}$)|(^(49030)[0-1]\d{10}$)|(^(49033)[0-4]\d{10}$)|(^(49110)[^12]\d{10}$)|(^(49117)[0-3]\d{10}$)|(^(49118)[^0-2]\d{10}$)|(^(493)[^6]\d{12}$)@', $strNumber))
		{
			return 'visa';
		}
		elseif (preg_match('@(^(5[0678])\d{11,18}$) |(^(6[^0357])\d{11,18}$) |(^(601)[^1]\d{9,16}$) |(^(6011)\d{9,11}$) |(^(6011)\d{13,16}$) |(^(65)\d{11,13}$) |(^(65)\d{15,18}$) |(^(633)[^34](\d{9,16}$)) |(^(6333)[0-4](\d{8,10}$)) |(^(6333)[0-4](\d{12}$)) |(^(6333)[0-4](\d{15}$)) |(^(6333)[5-9](\d{8,10}$)) |(^(6333)[5-9](\d{12}$)) |(^(6333)[5-9](\d{15}$)) |(^(6334)[0-4](\d{8,10}$)) |(^(6334)[0-4](\d{12}$)) |(^(6334)[0-4](\d{15}$)) |(^(67)[^(59)](\d{9,16}$)) |(^(6759)](\d{9,11}$)) |(^(6759)](\d{13}$)) |(^(6759)](\d{16}$)) |(^(67)[^(67)](\d{9,16}$)) |(^(6767)](\d{9,11}$)) |(^(6767)](\d{13}$)) |(^(6767)](\d{16}$))@', $strNumber))
		{
			return 'maestro';
		}
		elseif (preg_match('@^5[1-5]\d{14}$@', $strNumber))
		{
			return 'mc';
		}
		elseif (preg_match('@(^(6011)\d{12}$)|(^(65)\d{14}$)@', $strNumber))
		{
			return 'discover';
		}
		elseif (preg_match('@(^3[47])((\d{11}$)|(\d{13}$))@', $strNumber))
		{
			return 'amex';
		}
		elseif (preg_match('@(^(6334)[5-9](\d{11}$|\d{13,14}$)) |(^(6767)(\d{12}$|\d{14,15}$))@', $strNumber))
		{
			return 'solo';
		}
		elseif (preg_match('@(^(49030)[2-9](\d{10}$|\d{12,13}$)) |(^(49033)[5-9](\d{10}$|\d{12,13}$)) |(^(49110)[1-2](\d{10}$|\d{12,13}$)) |(^(49117)[4-9](\d{10}$|\d{12,13}$)) |(^(49118)[0-2](\d{10}$|\d{12,13}$)) |(^(4936)(\d{12}$|\d{14,15}$)) |(^(564182)(\d{11}$|\d{13,14}$)) |(^(6333)[0-4](\d{11}$|\d{13,14}$)) |(^(6759)(\d{12}$|\d{14,15}$))@', $strNumber))
		{
			return 'switch';
		}
		elseif (preg_match('@(^(352)[8-9](\d{11}$|\d{12}$))|(^(35)[3-8](\d{12}$|\d{13}$))@', $strNumber))
		{
			return 'jcb';
		}
		elseif (preg_match('@(^(30)[0-5]\d{11}$)|(^(36)\d{12}$)|(^(38[0-8])\d{11}$)@', $strNumber))
		{
			return 'diners';
		}
		elseif (preg_match('@^(389)[0-9]{11}$@', $strNumber))
		{
			return 'cartblanche';
		}
		elseif (preg_match('@(^(2014)|^(2149))\d{11}$@', $strNumber))
		{
			return 'enroute';
		}
		elseif (preg_match('@(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))@', $strNumber))
		{
			return 'ukdebit';
		}
		
		return false;
	}
}

