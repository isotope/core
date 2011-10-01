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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
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
						return $this->Isotope->calculatePrice((($fltPrice * $this->Isotope->Cart->products) + $this->calculateSurcharge()), $this, 'price', $this->arrData['tax_class']);

					case 'perItem':
						return $this->Isotope->calculatePrice((($fltPrice * $this->Isotope->Cart->items) + $this->calculateSurcharge()), $this, 'price', $this->arrData['tax_class']);

					default:
						return $this->Isotope->calculatePrice(($fltPrice + $this->calculateSurcharge()), $this, 'price', $this->arrData['tax_class']);
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

