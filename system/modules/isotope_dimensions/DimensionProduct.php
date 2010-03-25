<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class DimensionProduct extends IsotopeProduct
{
	
	/**
	 * Get a property
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'price':
			case 'price_override':
				$intPrice = 0;
				$arrMin = deserialize($this->arrData['dimensions_min']);
				$arrMax = deserialize($this->arrData['dimensions_max']);
				if ($this->arrOptions['dimension_x'] >= $arrMin[0] && $this->arrOptions['dimension_x'] <= $arrMax[0] && $this->arrOptions['dimension_y'] >= $arrMin[1] && $this->arrOptions['dimension_y'] <= $arrMax[1])
				{
					$objPrice = $this->Database->prepare("SELECT * FROM tl_product_dimension_prices WHERE pid=? AND dimension_x >= ? AND dimension_y >= ? ORDER BY dimension_x, dimension_y")->limit(1)->execute($this->arrData['dimensions'], $this->arrOptions['dimension_x'], $this->arrOptions['dimension_y']);
					
					if ($objPrice->numRows)
					{
						$intPrice = $objPrice->price;
					}
				}
				
				if (!$intPrice)
				{
					$objPrice = $this->Database->prepare("SELECT * FROM tl_product_dimension_prices WHERE pid=? AND dimension_x >= ? AND dimension_y >= ? ORDER BY dimension_x, dimension_y")->limit(1)->execute($this->arrData['dimensions'], $arrMin[0], $arrMin[1]);
					
					$intPrice = $objPrice->price;
				}
				
				return $this->Isotope->calculatePrice($intPrice, $this->arrData['tax_class']);
				break;
		}
		
		return parent::__get($strKey);
	}
	
	
	/**
	 * Set a property
	 */
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			case 'dimension_x':
			case 'dimension_y':
				$this->arrData[$strKey] = $varValue;
				break;
				
			default:
				parent::__set($strKey, $varValue);
		}
	}
	
	/**
	 * Return all attributes for this product
	 */
	public function getAttributes()
	{
		$arrData = parent::getAttributes();
		
		$arrData['dimension_x'] = intval($this->arrData['dimension_x']);
		$arrData['dimension_y'] = intval($this->arrData['dimension_y']);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['dimension_x'] = array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['dimension_x'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true),
			'attributes'			=> array('is_customer_defined'	=> true),		);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['dimension_y'] = array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['dimension_y'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true),
			'attributes'			=> array('is_customer_defined'	=> true),
		);
			
		return $arrData;
	}
}

