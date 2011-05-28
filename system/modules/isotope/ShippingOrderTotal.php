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
				$fltEligibleSubTotal = $this->getAdjustedSubTotal((TL_MODE=='FE' ? $this->Isotope->Cart->subTotal : $this->Isotope->Order->subTotal));

				return $fltEligibleSubTotal <= 0 ? 0.00 : $this->Isotope->calculatePrice($this->calculateShippingRate($this->id, $fltEligibleSubTotal), $this, 'price', $this->arrData['tax_class']);

			default:
				return parent::__get($strKey);
		}
	}

	/* protected function getRateLabel($strOptionName)
	{
		$arrOptionInfo = split('_', $strOptionName);

		$objRateLabel = $this->Database->prepare("SELECT name FROM tl_iso_shipping_options WHERE pid=? AND id=?")
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
		$objRates = $this->Database->prepare("SELECT * FROM tl_iso_shipping_options WHERE pid=?")
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
			if((float)$row['minimum_total']>0 && $fltCartSubTotal>=(float)$row['minimum_total'])
			{
				if($fltCartSubTotal<=(float)$row['maximum_total'] || $row['maximum_total']==0)
				{
					$fltRate = $row['rate'];
				}
			}
			elseif((float)$row['maximum_total']>0 && $fltCartSubTotal<=(float)$row['maximum_total'])
			{
				if($fltCartSubTotal>=(float)$row['minimum_total'])
				{
					$fltRate = $row['rate'];
				}
			}

		}

		return $fltRate;

	}

	/**
	 * shipping exempt items should be subtracted from the subtotal
	 * @param float
	 * @return float
	 */
	public function getAdjustedSubTotal($fltSubtotal)
	{

		$arrProducts = (TL_MODE=='FE' ? $this->Isotope->Cart->getProducts() : $this->Isotope->Order->getProducts());

		foreach($arrProducts as $objProduct)
		{
			if($objProduct->shipping_exempt)
			{
				$fltSubtotal -= ($objProduct->price * $objProduct->quantity_requested);
			}

		}

		return $fltSubtotal;
	}


	/**
	 * Initialize the module options DCA in backend
	 *
	 * @access public
	 * @return string
	 */
	public function moduleOptionsLoad()
	{
		$GLOBALS['TL_DCA']['tl_iso_shipping_options']['palettes']['default'] = '{general_legend},name,description;{config_legend},rate,minimum_total,maximum_total';
	}


	/**
	 * List module options in backend
	 *
	 * @access public
	 * @return string
	 */
	public function moduleOptionsList($row)
	{
		return '
<div class="cte_type ' . $key . '"><strong>' . $row['name'] . '</strong></div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h52' : '') . ' block">
'. $GLOBALS['TL_LANG']['tl_iso_shipping_options']['option_type'][0] . ': ' . $GLOBALS['TL_LANG']['tl_iso_shipping_options']['types'][$row['option_type']] . '<br /><br />' . $row['rate'] .' for '. $row['upper_limit'] . ' based on ' . $row['dest_country'] .', '. $row['dest_region'] . ', ' . $row['dest_zip'] . '</div>' . "\n";
	}
}

