<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class IsotopeSellout extends Frontend
{
	
	public function addProductToCollection($objProduct, $intQuantity, $objCollection)
	{
		if ($objCollection instanceof IsotopeCart)
		{
			$arrAttributes = $objProduct->getAttributes();
			
			if (array_key_exists('stock_quantity', $arrAttributes))
			{
				if ($objProduct->stock_quantity < 1)
				{
					return 0;
				}
				
				$arrProducts = $objCollection->getProducts();
				foreach( $arrProducts as $objCartProduct )
				{
					if ($objProduct->id == $objCartProduct->id)
					{
						$intAvailable = $objProduct->stock_quantity - $objCartProduct->quantity_requested;
						
						return $intAvailable < $intQuantity ? $intAvailable : $intQuantity;
					}
				}
				
				return $objProduct->stock_quantity < $intQuantity ? $objProduct->stock_quantity : $intQuantity;
			}
		}
		
		return $intQuantity;
	}
	
	
	public function writeOrder($orderId, $blnCheckout, $objModule)
	{
		$this->import('Isotope');
		
		$blnCartChange = false;
		$arrProducts = $this->Isotope->Cart->getProducts();
		
		foreach( $arrProducts as $objProduct )
		{
			$arrAttributes = $objProduct->getAttributes();
			
			if (array_key_exists('stock_quantity', $arrAttributes) && $objProduct->stock_quantity < $objProduct->quantity_requested)
			{
				$this->Isotope->Cart->updateProduct($objProduct, array('product_quantity'=>$objProduct->stock_quantity));
				$blnCartChange = true;
			}
		}
		
		// Subtract stock_quantity
		if (!$blnCartChange && $blnCheckout)
		{
			foreach( $arrProducts as $objProduct )
			{
				$arrAttributes = $objProduct->getAttributes();
				
				if (array_key_exists('stock_quantity', $arrAttributes) && $objProduct->stock_quantity < $objProduct->quantity_requested)
				{
					$this->Database->prepare("UPDATE tl_iso_products SET stock_quantity=? WHERE id=?")
								   ->execute(($objProduct->stock_quantity - $objProduct->quantity_requested), $objProduct->id);
				}
			}
		}
		
		return $blnCartChange ? false : $blnCheckout;
	}
}

