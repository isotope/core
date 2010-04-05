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
				$this->import('IsotopeCart', 'Cart');
				$fltEligibleSubTotal = $this->getAdjustedSubTotal($this->Cart->subTotal);
		
				if($fltEligibleSubTotal<=0)
				{
					return 0.00;
				}
				
				return $this->calculateShippingRate($this->id, $fltEligibleSubTotal);
				break;
			
			case 'optionsPrice':
	
				$arrOptionNames = split(',', $_SESSION['FORM_DATA']['shipping_options']);
	
				foreach($arrOptionNames as $option)
				{		
					$fltShippingOptionsTotal += $_SESSION['FORM_DATA'][$option];
		 		}
				
				return $fltShippingOptionsTotal;
				break;
				
			case 'optionsList':
			
				$arrOptionNames = split(',', $_SESSION['FORM_DATA']['shipping_options']);
				
				foreach($arrOptionNames as $option)
				{	
					$arrShippingOptionsList[] = $this->getRateLabel($option);
				}
				
				return implode(',', $arrShippingOptionsList);
				break;				
		}
		
		return parent::__get($strKey);
	}
	
	protected function getRateLabel($strOptionName)
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
	}
	
	
	public function calculateShippingRate($intPid, $fltCartSubTotal)
	{
		$this->import('FrontendUser','User');
		$this->import('Isotope');
			
		$arrUserGroups = deserialize($this->User->groups);
	
		$arrShippingAddress = $this->Isotope->getAddress('shipping'); //Tax calculated based on billing address.
				
		$objRates = $this->Database->prepare("SELECT * FROM tl_shipping_options WHERE pid=?")
								   ->execute($intPid);
		
		if($objRates->numRows < 1)
		{
			return 0;		
		}
	
		$arrData = $objRates->fetchAllAssoc();
		
		//sort by groups data
		foreach($arrData as $row)
		{
			$arrGroups = deserialize($row['groups']);			
			
			//
			if(sizeof($arrGroups))
			{
				foreach($arrGroups as $group)
				{
					switch($row['option_type'])
					{
						case 'ot_tier':
							$arrBaseRatesByMemberGroups[] = array
							(
								'group'			=> (integer)$group,
								'rate_info'		=> $row
							);
							break;
						case 'surcharge':
							$arrSurchargesByMemberGroups[] = array
							(
								'group'			=> (integer)$group,
								'rate_info'		=> $row
							);
					}
				}			
			}else{
				switch($row['option_type'])
				{
					case 'ot_tier':
						$arrBaseRates[] = array
						(
							'group'			=> 0,
							'rate_info'		=> $row
						);
						break;
					case 'surcharge':
						$arrSurcharges[] = array
						(
							'group'			=> 0,
							'rate_info'		=> $row
						);
						break;
					default:
						break;
				}
			}

		}
	
	
				
		//get the basic rate - calculate it based on group '0' first, which is the default, then any group NOT 0.
		foreach($arrBaseRates as $rate)
		{
			$arrCountries = deserialize($rate['rate_info']['dest_countries']);
				
			if(!is_array($arrCountries))
			{	
				$arrCountries = $this->getShippingModuleCountries($rate['rate_info']['pid']);
			}
			
			if((is_array($arrCountries) && in_array($arrShippingAddress['country'], $arrCountries)) || !is_array($arrCountries))
			{
				//determine value ranges
				foreach($rate['rate_info'] as $k=>$v)
				{
					$fltLimit = !is_null($rate['rate_info']['limit_value']) ? $rate['rate_info']['limit_value'] : 0;
					
					switch($k)
					{
						case 'limit_type':
							switch($v)
							{
								case 'lower':
									if($fltLimit!=0 && ((float)$fltCartSubTotal > (float)$fltLimit))
									{	
										$arrEligibleRates[] = $rate['rate_info']['rate'];						
									}
									break;
								case 'upper':						
									if($fltLimit!=0 && ((float)$fltLimit) >= (float)$fltCartSubTotal)
									{
										$arrEligibleRates[] = $rate['rate_info']['rate'];
									}
									break;
								default:
									break;
							}
						default:
							break;				
					}
				}	
			}
			
		}
		
		//Member groups rules will override base rate rules if there is a match.
		if(is_array($arrBaseRatesByMemberGroups))
		{
		
			foreach($arrBaseRatesByMemberGroups as $rate)
			{
				$arrCountries = deserialize($rate['rate_info']['dest_countries']);
				
				if(!is_array($arrCountries))
				{	
					$arrCountries = $this->getShippingModuleCountries($rate['rate_info']['pid']);
				}				
			
				//$arrRegions = deserialize($rate['rate_info']['dest_regions']);
				//$arrPostalCodes = split(',', trim($rate['rate_info']['dest_postalcodes']));
				
				if(in_array($rate['group'], $arrUserGroups)) //is this rate a candidate rate for this member's group?
				{
					if((is_array($arrCountries) && in_array($arrShippingAddress['country'], $arrCountries)) || !is_array($arrCountries))
					{

						//determine value ranges
						foreach($rate['rate_info'] as $k=>$v)
						{
							
							$fltLimit = !is_null($rate['rate_info']['limit_value']) ? $rate['rate_info']['limit_value'] : 0;
							
							switch($k)
							{
								case 'limit_type':
								
									switch($v)
									{
										case 'lower':
											
											if($fltLimit!=0 && ((float)$fltCartSubTotal >= (float)$fltLimit))
											{	
												$arrEligibleRates[] = $rate['rate_info']['rate'];
											}
											break;
										case 'upper':
																							
											if($fltLimit!=0 && ((float)$fltLimit >= (float)$fltCartSubTotal))
											{
												$arrEligibleRates[] = $rate['rate_info']['rate'];
											}
											break;
										default:
											break;
									}
								default:
									break;				
							}
						
						}
					}	
				}
			}
			
		}
		
		$fltBaseRate = min($arrEligibleRates);
		
		//get the basic rate - calculate it based on group '0' first, which is the default, then any group NOT 0.
		foreach($arrSurcharges as $rate)
		{
			$arrCountries = deserialize($rate['rate_info']['dest_countries']);
				
			if(!is_array($arrCountries))
			{	
				$arrCountries = $this->getShippingModuleCountries($rate['rate_info']['pid']);
			}
		
			if((is_array($arrCountries) && in_array($arrShippingAddress['country'], $arrCountries)) || !is_array($arrCountries))
			{

				//determine value ranges
				foreach($rate['rate_info'] as $k=>$v)
				{
					$fltRate = !is_null($rate['rate_info']['rate']) ? $rate['rate_info']['rate'] : 0;
					
					switch($k)
					{
						case 'mandatory':
							switch($v)
							{
								case true:
									$arrSurcharges[] = $rate['rate_info']['rate'];
									break;
								default:						
									$this->shipping_options[$rate['rate_info']['pid']] = $rate['rate_info']['id'];
									break;
							}
						default:
							break;				
					}
				}
			}	
			
		}

		if(is_array($arrSurchargesByMemberGroups))
		{
			//get the basic rate - calculate it based on group '0' first, which is the default, then any group NOT 0.
			foreach($arrSurchargesByMemberGroups as $rate)
			{
				$arrCountries = deserialize($rate['rate_info']['dest_countries']);
					
				if(!is_array($arrCountries))
				{	
					$arrCountries = $this->getShippingModuleCountries($rate['rate_info']['pid']);
				}
				
				if((is_array($arrCountries) && in_array($arrShippingAddress['country'], $arrCountries)) || !is_array($arrCountries))
				{			
					//determine value ranges
					foreach($rate['rate_info'] as $k=>$v)
					{
						
							$fltRate = !is_null($rate['rate_info']['rate']) ? $rate['rate_info']['rate'] : 0;
							
							switch($k)
							{
								case 'mandatory':
									switch($v)
									{
										case true:
											$arrSurcharges[] = $rate['rate_info']['rate'];
											break;
										default:
											$this->shipping_options[$rate['rate_info']['pid']] = $rate['rate_info']['id'];
											break;
									}
									break;
									
								default:
									break;				
							}
			
					}
				}	
				
			}
		
		}
		 		
 		$fltTotalSurcharges = array_sum($arrSurcharges);
							
		$fltShippingTotal = $fltBaseRate + $fltTotalSurcharges;
		
		if($this->Input->post('shipping_options'))
		{
			$_SESSION['FORM_DATA']['shipping_options'] = $this->Input->post('shipping_options');
			$strOptionName = $_SESSION['FORM_DATA']['shipping_options'];
			$_SESSION['FORM_DATA'][$strOptionName] = $this->Input->post($strOptionName);
		}
		
		return $fltShippingTotal;
		
	}

	public function getAdjustedSubTotal($fltSubtotal)
	{
		$this->import('Isotope');
		
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
		
	/*
	,
      'fields' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_catalog_types']['fields'],
				'href'                => 'table=tl_catalog_fields',
				'icon'                => 'tablewizard.gif',
        'button_callback'     => array('tl_catalog_types', 'fieldsButton')
			)
	*/
	public function getShippingOptions($intModuleId)
	{
		$strOptions = is_array($this->shipping_options) && sizeof($this->shipping_options)>0 ? join(',', $this->shipping_options) : 0;
				
		$objShippingModule = $this->Database->prepare("SELECT sm.*, so.* FROM tl_shipping_modules sm INNER JOIN tl_shipping_options so ON so.pid=sm.id WHERE sm.id=? AND so.id IN(" . $strOptions . ") AND so.mandatory!='1'")
											->execute($intModuleId);
		
		if($objShippingModule->numRows < 1)
		{
			return false;
		}
		
		$arrShippingOptions = $objShippingModule->fetchAllAssoc();
		
		
		//option naming convention - 'shipping_option_' . $rate['rate_info']['pid'] . '_' . $rate['rate_info']['id']
		foreach($arrShippingOptions as $option)
		{
		
			$strOption .= sprintf('<label for="ctrl_shipping_option_%s">%s (+' . $this->Isotope->formatPriceWithCurrency($option['rate']) . ')</label> <input type="checkbox" id="ctrl_shipping_option_%s" name="shipping_option_%s" value="' . $option['rate'] . '" /><br />%s',
			$option['pid'] . '_' . $option['id'], 
			$option['name'],
			$option['pid'] . '_' . $option['id'],
			$option['pid'] . '_' . $option['id'],
			($option['description'] ? '<br />(<em>' . $option['description'] . '</em>)' : null)		
			);
		
			$arrOptions[] = 'shipping_option_' . $option['pid'] . '_' . $option['id'];
		}
		
		if(is_array($arrOptions))
		{
			$strOption .= '<input type="hidden" name="shipping_options" value="' . join(',', $arrOptions) . '" />';
		}
		
		return $strOption;
	}
	
	protected function getShippingModuleCountries($intModuleId)
	{
		$objCountries = $this->Database->prepare("SELECT countries FROM tl_shipping_modules WHERE id=?")
									   ->limit(1)
									   ->execute($intModuleId);
		
		if($objCoutries->numRows < 1)
		{
			return null;
		}
		
		return deserialize($objCountries->countries);
	}

	public function moduleOperations($intId)
	{
		/*$this->import('BackendUser', 'User');
	
		if (!$this->User->isAdmin)
		{
			return '';
		}*/
	
		return '<a href="'.$this->Environment->request.'&amp;table=tl_shipping_options&amp;id=' . $intId . '" title="'.specialchars($title).'"'.$attributes.'>test</a>'; //'.$this->generateImage('tablewizard.gif', 'rates table').'</a> ';

	}
	
	protected function sortByUpperLimits($varValue1, $varValue2)
	{
		switch($varValue1['limit_type'])
		{
			case 'upper':
				return $varValue2['limit_type']=='upper' ? ($varValue1['limit_value'] < $varValue2['limit_value'] ? 1 : -1) : null;
				break;			
		}
		
	}
}

