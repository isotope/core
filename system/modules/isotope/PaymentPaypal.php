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
 * Handle Paypal payments
 * 
 * @extends Payment
 */
class PaymentPaypal extends Payment
{
	
	/**
	 * Return a list of payment options this module provides.
	 * 
	 * @access public
	 * @return array
	 */
	public function getPaymentOptions()
	{
		$arrOptions = array();
		
		$arrCc = deserialize($this->creditcards);
		
		if (is_array($arrCc) && count($arrCc))
		{
			foreach( $arrCc as $cc )
			{
				$arrOptions[$cc] = strlen($GLOBALS['TL_LANG']['ISO'][$cc]) ? $GLOBALS['TL_LANG']['ISO'][$cc] : $cc;
			}
		}
		
		if ($this->allow_paypal)
		{
			$arrOptions['paypal'] = strlen($GLOBALS['TL_LANG']['ISO']['paypal']) ? $GLOBALS['TL_LANG']['ISO']['paypal'] : 'paypal';
		}
		
		return $arrOptions;
	}
	
	
	/**
	 * processPayment function.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		// Reload page every 5 seconds and check if payment was successful
		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="5,http://...">';
	}
}