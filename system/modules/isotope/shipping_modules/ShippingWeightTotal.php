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

namespace Isotope;


/**
 * Class ShippingWeightTotal
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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


	/**
	 * Get the checkout surcharge for this shipping method
	 */
	public function getSurcharge($objCollection)
	{
		$objOptions = $this->getOptions();

		if (!$objOptions->numRows || $objOptions->rate == 0)
		{
			return false;
		}

		return $this->Isotope->calculateSurcharge(
								$objOptions->rate,
								($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->label . ')'),
								$this->arrData['tax_class'],
								$objCollection->getProducts(),
								$this);
	}
}

