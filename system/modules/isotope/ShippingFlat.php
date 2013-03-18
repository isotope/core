<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
				return $this->Isotope->calculatePrice($this->getPrice(), $this, 'price', $this->arrData['tax_class']);
				break;
		}

		return parent::__get($strKey);
	}


	/**
	 * Get the checkout surcharge for this shipping method
	 */
	public function getSurcharge($objCollection)
	{
		$fltPrice = $this->getPrice();

		if ($fltPrice == 0)
		{
			return false;
		}

		return $this->Isotope->calculateSurcharge(
								$fltPrice,
								($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->label . ')'),
								$this->arrData['tax_class'],
								$objCollection->getProducts(),
								$this);
	}


	/**
	 * Calculate the price based on module configuration
	 * @return float
	 */
	private function getPrice()
	{
		$strPrice = $this->arrData['price'];
		$blnPercentage = substr($strPrice, -1) == '%' ? true : false;

		if ($blnPercentage)
		{
			$fltSurcharge = (float)substr($strPrice, 0, -1);
			$fltPrice = $this->Isotope->Cart->subTotal / 100 * $fltSurcharge;
		}
		else
		{
			$fltPrice = (float)$strPrice;
		}

		switch( $this->flatCalculation )
		{
			case 'perProduct':
				return (($fltPrice * $this->Isotope->Cart->products) + $this->calculateSurcharge());

			case 'perItem':
				return (($fltPrice * $this->Isotope->Cart->items) + $this->calculateSurcharge());

			default:
				return ($fltPrice + $this->calculateSurcharge());
		}
	}


	/**
	 * Calculate surcharge from a product if the surcharge field is set in module settings
	 * @return float
	 */
	protected function calculateSurcharge()
	{
		if (!strlen($this->surcharge_field))
			return 0;

		$intSurcharge = 0;
		$arrProducts = $this->Isotope->Cart->getProducts();

		foreach( $arrProducts as $objProduct )
		{
			if ($this->flatCalculation == 'perItem')
			{
				$intSurcharge += ($objProduct->quantity_requested * floatval($objProduct->{$this->surcharge_field}));
			}
			else
			{
				$intSurcharge += floatval($objProduct->{$this->surcharge_field});
			}
		}

		return $intSurcharge;
	}
}

