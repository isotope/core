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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

/**
 * Parent class for all payment gateway modules
 * 
 * @extends Frontend
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
		$this->import('IsotopeCart', 'Cart');
		
		switch( $strKey )
		{
			case 'available':
				if (($this->minimum_total > 0 && $this->minimum_total > $this->Cart->subTotal) || ($this->minimum_total > 0 && $this->maximum_total < $this->Cart->subTotal))
					return false;
					
				$arrAllowed = deserialize($this->shipping_modules);
				if (!is_array($arrAllowed) || !count($arrAllowed) || !in_array($_SESSION['FORM_DATA']['shipping']['module'], $arrAllowed))
					return false;
					
				if (!$this->enabled && !BE_USER_LOGGED_IN)
					return false;
					
				return true;
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
	public function statusOptions($objModule)
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
	 * @abstract
	 * @access public
	 * @return void
	 */
	public function processPostSale() {}
	
	
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
}

