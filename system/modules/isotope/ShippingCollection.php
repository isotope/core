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
 * Parent class for all shipping gateway modules
 * 
 * @extends Frontend
 */
class ShippingCollection extends Shipping
{
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
				switch($this->type)
				{
				
					case 'flat':				
						switch( $this->flatCalculation )
						{
							case 'perProduct':
								return (($this->arrData['price'] * $this->Cart->products) + $this->calculateSurcharge());
								
							case 'perItem':
								return (($this->arrData['price'] * $this->Cart->items) + $this->calculateSurcharge());
								
							default:
								return ($this->arrData['price'] + $this->calculateSurcharge());
						}
						break;
					case 'collection':
						return $this->calculateShippingRate($this->id, $this->Cart->subTotal);
						break;
				}
				break;
					
		}
		
		return parent::__get($strKey);
	}
	
	
	protected function calculateSurcharge()
	{
		if (!strlen($this->surcharge_field))
			return 0;
			
		$intSurcharge = 0;
		$arrProducts = $this->Cart->getProducts();
		
		foreach( $arrProducts as $product )
		{
			// Exclude this product if table does not have this field
			if ($this->Database->fieldExists($this->surcharge_field, $product['storeTable']))
			{
				$strSurcharge = $this->Database->prepare("SELECT * FROM " . $product['storeTable'] . " WHERE id=?")
											   ->limit(1)
											   ->execute($product['id'])
											   ->{$this->surcharge_field};
											   
				if ($this->flatCalculation == 'perItem')
				{
					$intSurcharge += ($product['quantity_requested'] * floatval($strSurcharge));
				}
				else
				{
					$intSurcharge += floatval($strSurcharge);
				}
			}
		}
		
		return $intSurcharge;
	}
	
	public function calculateShippingRate($intPid, $fltCartSubTotal)
	{
		$objRates = $this->Database->prepare("SELECT * FROM tl_shipping_options WHERE pid=? ORDER BY upper_limit")
								   ->execute($intPid);
		
		if($objRates->numRows < 1)
		{
			return 0;		
		}
	
		while( $objRates->next() )
		{
			if($objRates->upper_limit > (float)$fltCartSubTotal)	//TODO: the comparison should be dynamic.
			{	
				return $objRates->rate;
			}
		}
		
		return 0;
		
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
		$objShippingModule = $this->Database->prepare("SELECT sm.*, so.* FROM tl_shipping_modules sm INNER JOIN tl_shipping_options so ON so.pid=sm.id WHERE sm.id=?")											->execute($intModuleId);
		
		if($objShippingModule->numRows < 1)
		{
			return '';
		}
		
		$arrShippingData = $objShippingModule->fetchAllAssoc();
		var_dump($arrShippingData);
		
		return '';
		//return '<label for="ctrl_payment_module_option_%s">Expedited Shipping (Add ' . $this->Isotope->formatPriceWithCurrency($option['price']) . '</label> <input type="checkbox" id="ctrL_payment_module_option_%s" name="payment_option" value="' . $this->getOptionValue() . '" />';
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
}

