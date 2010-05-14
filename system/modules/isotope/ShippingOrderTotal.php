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
 

class ShippingOrderTotal extends IsotopeShipping
{
	protected $shipping_options = array();

	/**
	 * Initialize the object
	 *
	 * @access public
	 * @param array $arrRow
	 */
	public function __construct($arrRow)
	{
		parent::__construct($arrRow);
					
		$this->arrData = $arrRow;
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
			case 'price':
				$fltEligibleSubTotal = $this->getAdjustedSubTotal($this->Cart->subTotal);
		
				if($fltEligibleSubTotal<=0)
				{
					return 0.00;
				}
				
				return $this->calculateShippingRate($this->id, $fltEligibleSubTotal);
				break;
		}
		
		return parent::__get($strKey);
	}
	
	/* protected function getRateLabel($strOptionName)
	{
		$arrOptionInfo = split('_', $strOptionName);
	
		$objRateLabel = $this->Database->prepare("SELECT name FROM tl_shipping_options WHERE pid=? AND id=?")
									   ->limit(1)
									   ->execute($arrOptionInfo[2], $arrOptionInfo[3]);
		
		if($objRateLabel->numRows < 1)
		{
			return false;
		}
		
		return $objRateLabel->name;
	}*/
	
	
	public function calculateShippingRate($intPid, $fltCartSubTotal)
	{			
		$objRates = $this->Database->prepare("SELECT * FROM tl_shipping_options WHERE pid=?")
								   ->execute($intPid);
		
		if($objRates->numRows < 1)
		{
			return 0;		
		}
	
		$arrData = $objRates->fetchAllAssoc();
		
					
		//get the basic rate - calculate it based on group '0' first, which is the default, then any group NOT 0.
		foreach($arrData as $row)
		{		
			//determine value ranges
			foreach($row as $k=>$v)
			{										
				switch($k)
				{
					case ('minimum_total' && $v>0 && $fltSubTotal>=$v):
						$arrEligibleRates[] = $row['rate'];
						break;
					case ('maximum_total' && $v>0 && $fltSubTotal<=$v):
						$arrEligibleRates[] = $row['rate'];
						break;
					default:
						break;				
				}
			}				
		}
		
				
		$fltShippingTotal = min($arrEligibleRates);
				
		return $fltShippingTotal;
		
	}

	/** 
	 * shipping exempt items should be subtracted from the subtotal
	 * @param float
	 * @return float
	 */
	public function getAdjustedSubTotal($fltSubtotal)
	{
		
		$arrProducts = $this->Cart->getProducts();
		
		foreach($arrProducts as $objProduct)
		{
			if($objProduct->shipping_exempt)
			{
				$fltSubtotal -= $objProduct->price;
			}
		
		}
		
		return $fltSubtotal;
	}
		
		
	public function moduleOperations()
	{
		/*$this->import('BackendUser', 'User');
	
		if (!$this->User->isAdmin)
		{
			return '';
		}*/
	
		return '<a href="'.str_replace('tl_shipping_modules','tl_shipping_options',$this->Environment->request).'&amp;id=' . $this->id . '" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage('tablewizard.gif', 'rates table').'</a>'; //'.$this->generateImage('tablewizard.gif', 'rates table').'</a> ';

	}
	
}

