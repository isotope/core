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
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class PaymentPayone extends IsotopePayment
{

	/**
	 * Process checkout payment.
	 *
	 * @access public
	 * @return mixed
	 */
	public function processPayment()
	{
		return true;
	}


	/**
	 * HTML form for checkout
	 *
	 * @access public
	 * @return mixed
	 */
	public function checkoutForm()
	{
		$i = 0;
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Isotope->Cart->id);

		$arrData = array
		(
			'aid'				=> $this->payone_aid,
			'portalid'			=> $this->payone_portalid,
			'mode'				=> ($this->debug ? 'test' : 'live'),
			'request'			=> ($this->trans_type=='auth' ? 'preauthorization' : 'authorization'),
			'encoding'			=> 'UTF-8',
			'clearingtype'		=> $this->payone_clearingtype,
			'reference'			=> $objOrder->order_id,
			'display_name'		=> 'no',
			'display_address'	=> 'no',
			'successurl'		=> $this->Environment->base . $this->addToUrl('step=complete', true) . '?txid=__txid__',
			'backurl'			=> $this->Environment->base . $this->addToUrl('step=failed', true),
			'amount'			=> ($this->Isotope->Cart->grandTotal * 100),
			'currency'			=> $this->Isotope->Config->currency,
		);

		foreach( $this->Isotope->Cart->getProducts() as $objProduct )
		{
			$strOptions = '';
			$arrOptions = $objProduct->getOptions();

			if (is_array($arrOptions) && count($arrOptions))
			{
				$options = array();

				foreach( $arrOptions as $option )
				{
					$options[] = $option['label'] . ': ' . $option['value'];
				}

				$strOptions = ' ('.implode(', ', $options).')';
			}

			$arrData['id['.++$i.']']	= $objProduct->sku;
			$arrData['pr['.$i.']']		= round($objProduct->price, 2) * 100;
			$arrData['no['.$i.']']		= $objProduct->quantity_requested;
			$arrData['de['.$i.']']		= specialchars($objProduct->name . $strOptions);
		}

		foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge )
		{
			if ($arrSurcharge['add'] === false)
				continue;

			$arrData['de['.++$i.']']	= $arrSurcharge['label'];
			$arrData['pr['.$i.']']		= $arrSurcharge['total_price'] * 100;
		}


		ksort($arrData);
		$strHash = md5(implode('', $arrData) . $this->payone_key);

		$strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_payone'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_payone'][1] . '</p>
<form id="payment_form" action="https://secure.pay1.de/frontend/" method="post">';

		foreach( $arrData as $k => $v )
		{
			$strBuffer .= "\n" . '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
		}

		$strBuffer .= '
<input type="hidden" name="hash" value="' . $strHash . '" />

<input type="hidden" name="company" value="' . $this->Isotope->Cart->billingAddress['company'] . '">
<input type="hidden" name="firstname" value="' . $this->Isotope->Cart->billingAddress['firstname'] . '">
<input type="hidden" name="lastname" value="' . $this->Isotope->Cart->billingAddress['lastname'] . '">
<input type="hidden" name="street" value="' . $this->Isotope->Cart->billingAddress['street_1'] . '">
<input type="hidden" name="zip" value="' . $this->Isotope->Cart->billingAddress['postal'] . '">
<input type="hidden" name="city" value="' . $this->Isotope->Cart->billingAddress['city'] . '">
<input type="hidden" name="country" value="' . strtoupper($this->Isotope->Cart->billingAddress['country']) . '">
<input type="hidden" name="email" value="' . $this->Isotope->Cart->billingAddress['email'] . '">
<input type="hidden" name="telephonenumber" value="' . $this->Isotope->Cart->billingAddress['phone'] . '">
<input type="hidden" name="language" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '" />

<input type="submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_payone'][2]) . '">
</form>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent( \'domready\' , function() {
  $(\'payment_form\').submit();
});
//--><!]]>
</script>';

		return $strBuffer;
	}


	/**
	 * Return a list of valid credit card types for this payment module
	 */
	public function getAllowedCCTypes()
	{
		return array();
	}
}

