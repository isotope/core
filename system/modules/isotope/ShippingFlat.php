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
 

class ShippingFlat extends IsotopeShipping
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
				switch( $this->flatCalculation )
				{
					case 'perProduct':
						return $this->Isotope->calculatePrice(($this->arrData['price'] * $this->Cart->products) + $this->calculateSurcharge());
						
					case 'perItem':
						return $this->Isotope->calculatePrice(($this->arrData['price'] * $this->Cart->items) + $this->calculateSurcharge());
						
					default:
						return $this->Isotope->calculatePrice($this->arrData['price'] + $this->calculateSurcharge());
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
		
		foreach( $arrProducts as $objProduct )
		{
			// Exclude this product if table does not have this field
			if ($this->Database->fieldExists($this->surcharge_field, 'tl_product_data'))
			{
				$strSurcharge = $this->Database->prepare("SELECT * FROM tl_product_data WHERE id=?")
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
}

