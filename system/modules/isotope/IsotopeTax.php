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
 * @copyright  Intelligent Spark 2010
 * @author     Fred Bliss <sales@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class IsotopeTax extends Controller
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_tax_class';
	
	/**
	 * Data array
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Address array
	 * @var array
	 */
	protected $arrAddress = array();
	
	/**
	 * Construct the object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Database');
		$this->import('Isotope');

	}
	
	/**
	 * Calculate tax for a certain tax class, based on the current user information 
	 */
	public function calculateTax($intTaxClass, $fltPrice, $blnAdd = false, $arrAddresses = array())
	{
		if(!count($arrAddresses))
			return 0;
			
		$objTaxClass = $this->Database->prepare("SELECT * FROM tl_tax_class WHERE id=?")->limit(1)->execute($intTaxClass);
		
		if(!$objTaxClass->numRows)
			return 0;
			
		$arrTaxes = array();
		/*
		$objIncludes = $this->Database->prepare("SELECT * FROM tl_tax_rate WHERE id=?")->limit(1)->execute($objTaxClass->includes);
				
		if ($objIncludes->numRows)
		{
		
			$arrTaxRate = deserialize($objIncludes->rate);
		
			// final price / (1 + (tax / 100)
			if (strlen($arrTaxRate['unit']))
			{
				$fltTax = $fltPrice - ($fltPrice / (1 + (floatval($arrTaxRate['value']) / 100)));
			}
			// Full amount
			else
			{
				$fltTax = floatval($arrTaxRate['value']);
			}
									
			if (!$this->useTaxRate($objIncludes, $fltPrice, $arrAddresses))
			{				
				$fltPrice -= $fltTax;
			}
			else
			{
				$arrTaxes[$objTaxClass->id.'_'.$objIncludes->id] = array
				(
					'label'			=> (strlen($objTaxClass->label) ? $objTaxClass->label : $objIncludes->label),
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $fltTax,
					'add'			=> false,
				);
			}
		}*/
		
		if (!$blnAdd)
		{
			return $fltPrice;
		}
		
		$arrRates = deserialize($objTaxClass->rates);
		if (!is_array($arrRates) || !count($arrRates))
			return $fltPrice;
		
		$objRates = $this->Database->execute("SELECT * FROM tl_tax_rate WHERE id IN (" . implode(',', $arrRates) . ") ORDER BY id=" . implode(" DESC, id=", $arrRates) . " DESC");

		while( $objRates->next() )
		{
			if ($this->useTaxRate($objRates, $fltPrice, $arrAddresses))
			{
				
				$arrTaxRate = deserialize($objRates->rate);
				
				// final price * (1 + (tax / 100)
				if (strlen($arrTaxRate['unit']))
				{
					$fltTax = ($fltPrice * (1 + (floatval($arrTaxRate['value']) / 100))) - $fltPrice;
				}
				// Full amount
				else
				{
					$fltTax = floatval($arrTaxRate['value']);
				}
				
				$arrTaxes[$objRates->id] = array
				(
					'label'			=> $objRates->label,
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $fltTax,
					'add'			=> true,
				);
				
				if ($objRates->stop)
					break;
			}
		}
		
		return $arrTaxes;
	}
	
	/** 
	 * Determine if the current address data falls within a taxable area
	 * @param object $objRate
	 * @param float $fltPrice
	 * @param array $arrAddressData
	 * @return boolean
	 */
	public function useTaxRate($objRate, $fltPrice, $arrAddressData = array())
	{		
		$arrAddressTypes = deserialize($objRate->address);
		
		if (is_array($arrAddressTypes) && count($arrAddressTypes))
		{
			foreach( $arrAddressTypes as $address )
			{				
				$arrAddress = $arrAddressData[$address . 'Address'];
				
				if (strlen($objRate->country) && $objRate->country != $arrAddress['country'])
					return false;

				
				if (strlen($objRate->subdivision) && $objRate->subdivision != $arrAddress['subdivision'])
					return false;

					
				$arrPostal = deserialize($objRate->postal);
				if (is_array($arrPostal) && count($arrPostal) && strlen($arrPostal[0]))
				{
					if (strlen($arrPostal[1]))
					{
						if ($arrPostal[0] > $arrAddress['postal'] || $arrPostal[1] < $arrAddress['postal'])
							return false;
					}
					else
					{
						if ($arrPostal[0] != $arrAddress['postal'])
							return false;
					}
				}
				
				$arrPrice = deserialize($objRate->amount);
				if (is_array($arrPrice) && count($arrPrice) && strlen($arrPrice[0]))
				{
					if (strlen($arrPrice[1]))
					{
						if ($arrPrice[0] > $fltPrice || $arrPrice[1] < $fltPrice)
							return false;
					}
					else
					{
						if ($arrPrice[0] != $fltPrice)
							return false;
					}
				}
			}
		}
			
		return true;
	}
	
	/**
	 * Return the current record as associative array
	 * @return array
	 */
	public function getData()
	{
		return $this->arrData;
	}
}