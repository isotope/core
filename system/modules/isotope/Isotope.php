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
 

class Isotope extends Controller
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;
	
	
	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct() {}
	
	
	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}
	
	
	/**
	 * Instantiate a database driver object and return it (Factory)
	 * @return object
	 * @throws Exception
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new Isotope();
		}

		return self::$objInstance;
	}


	public function formatPrice($fltPrice, $arrStoreConfig)
	{
		$arrFormat = $GLOBALS['ISO_NUM'][$arrStoreConfig['currencyFormat']];
		
		if (!is_array($arrFormat) || !count($arrFormat) == 3)
			return $fltPrice;
		
		return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
	}
	
	public function formatPriceWithCurrency($fltPrice, $arrStoreConfig, $blnHtml=false)
	{
		$strPrice = $this->formatPrice($fltPrice, $arrStoreConfig);
		
		$strCurrency = $arrStoreConfig['currency'];
		
		if ($arrStoreConfig['currencySymbol'] && strlen($GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency]))
		{
			$strCurrency = ($blnHtml ? '<span class="currency">' : '') . $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency] . ($blnHtml ? '</span>' : '');
		}
		else
		{
			$strCurrency = ($arrStoreConfig['currencyPosition'] == 'right' ? ' ' : '') . ($blnHtml ? '<span class="currency">' : '') . $strCurrency . ($blnHtml ? '</span>' : '') . ($arrStoreConfig['currencyPosition'] == 'left' ? ' ' : '');
		}
		
		if ($arrStoreConfig['currencyPosition'] == 'right')
		{
			return $strPrice . $strCurrency;
		}
		
		return $strCurrency . $strPrice;
	}
	
	
	/**
	 * getStoreConfigById function.
	 * 
	 * @todo cache results!
	 * @access public
	 * @param int $intStoreId
	 * @return array
	 */
	public function getStoreConfigById($intStoreId)
	{
		$this->import('Database');
		
		if(!$intStoreId)
		{
			return array();
		}
		
		$objStoreConfig = $this->Database->prepare("SELECT * FROM tl_store WHERE id=?")
										 ->limit(1)
										 ->execute($intStoreId);
		
		if(!$objStoreConfig->numRows)
		{
			return array();
		}
		
		return $objStoreConfig->fetchAssoc();
	}
}

