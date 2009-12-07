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
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

class ShippingUPS extends Shipping
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
				/*
				
				send XML request.s
				
				*/
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

