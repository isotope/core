<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Shipping;


/**
 * Class ShippingFlat
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Flat extends Shipping
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

