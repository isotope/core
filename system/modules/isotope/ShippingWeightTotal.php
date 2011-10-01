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


class ShippingWeightTotal extends IsotopeShipping
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
			case 'available':
				if (!parent::__get('available'))
					return false;

				$objOptions = $this->getOptions();
				if (!$objOptions->numRows)
					return false;

				return true;
				break;

			case 'price':
				$objOptions = $this->getOptions();
				return $this->Isotope->calculatePrice($objOptions->rate, $this, 'price', $this->arrData['tax_class']);

			default:
				return parent::__get($strKey);
		}
	}


	protected function getOptions()
	{
		$fltWeight = $this->Isotope->Cart->getShippingWeight($this->weight_unit);

		return $this->Database->execute("SELECT * FROM tl_iso_shipping_options WHERE pid={$this->id} AND enabled='1' AND (weight_from=0 OR weight_from <= $fltWeight) AND (weight_to=0 OR weight_to >= $fltWeight) ORDER BY rate");
	}


	/**
	 * Initialize the module options DCA in backend
	 *
	 * @access public
	 * @return string
	 */
	public function moduleOptionsLoad()
	{
		$GLOBALS['TL_DCA']['tl_iso_shipping_options']['palettes']['default'] = '{general_legend},name;{config_legend},weight_from,weight_to,rate;{enabled_legend},enabled';
		$GLOBALS['TL_DCA']['tl_iso_shipping_options']['list']['sorting']['headerFields'][] = 'weight_unit';
	}


	/**
	 * List module options in backend
	 *
	 * @access public
	 * @return string
	 */
	public function moduleOptionsList($row)
	{
		$key = $row['enabled'] ? 'published' : 'unpublished';

		return '
<div class="cte_type ' . $key . '"><strong>' . $row['name'] . '</strong></div>
<div class="block">
<table cellspacing="0" cellpadding="0" summary="Row details">
  <tbody>
    <tr>
      <td style="font-weight:bold">' . $GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_from'][0] . ':&nbsp;</td>
      <td>' . $row['weight_from'] . ' ' . $this->weight_unit . '</td>
    </tr>
    <tr>
      <td style="font-weight:bold">' . $GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_to'][0] . ':&nbsp;</td>
      <td>' . $row['weight_to'] . ' ' . $this->weight_unit . '</td>
    </tr>
    <tr>
      <td style="font-weight:bold">' . $GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'][0] . ':&nbsp;</td>
      <td>' . $row['rate'] . '</td>
    </tr>
  </tbody>
</table>
</div>';
	}
}

