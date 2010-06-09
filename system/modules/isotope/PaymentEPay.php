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
 
 
/**
 * Handle Paypal payments
 * 
 * @extends Payment
 */
class PaymentEPay extends IsotopePayment
{

	private $arrLanguages = array('da'=>1, 'en'=>2, 'sv'=>3, 'no'=>4, 'kl'=>5, 'is'=>6, 'de'=>7, 'fi'=>8);
	private $arrCurrencies = array('DKK'=>208, 'EUR'=>978, 'USD'=>840, 'NOK'=>578, 'SEK'=>752, 'GBP'=>826);
	
	
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'available':
				if (!array_key_exists($this->Isotope->Config->currency, $this->arrCurrencies))
					return false;
					
				return parent::__get($strKey);
				
			default:
				return parent::__get($strKey);
		}
	}
	
	
	/**
	 * Return a list of status options.
	 * 
	 * @access public
	 * @return array
	 */
	public function statusOptions()
	{
		return array('pending', 'processing', 'complete', 'on_hold');
	}
	
	
	/**
	 * processPayment function.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Isotope->Cart->id);
		$intTotal = str_replace('.', '', $this->Isotope->Cart->grandTotal);
		
		// Check basic order data
		if ($this->Input->get('orderid') == $objOrder->id && $this->Input->get('cur') == $this->arrCurrencies[$this->Isotope->Config->currency] && $this->Input->get('amount') == $intTotal)
		{
			// Validate MD5 secret key
			if (md5($intTotal . $objOrder->id . $this->Input->get('tid') . $this->epay_secretkey) == $this->Input->get('eKey'))
			{
				return true;
			}
		}
		
		$this->log('Invalid payment data received.', 'PaymentEPay processPayment()', TL_ERROR);
		
		$objTemplate = new FrontendTemplate('mod_message');
		$objTemplate->type = 'error';
		$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing_failed'];
		return $objTemplate->parse();
	}
	
	
	/**
	 * Return the payment form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		global $objPage;
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Isotope->Cart->id);
		$intTotal = str_replace('.', '', $this->Isotope->Cart->grandTotal);
		
		return '
<h2>' . $GLOBALS['TL_LANG']['ISO']['pay_with_epay'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['ISO']['pay_with_epay'][1] . '</p>
<form id="payment_form" action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post">

<input type="hidden" name="language" value="' . (in_array($GLOBALS['TL_LANGUAGE'], $this->arrLanguages) ? $this->arrLanguages[$GLOBALS['TL_LANGUAGE']] : 2) . '">
<input type="hidden" name="merchantnumber" value="' . $this->epay_merchantnumber . '">
<input type="hidden" name="orderid" value="' . $objOrder->id . '">
<input type="hidden" name="currency" value="' . $this->arrCurrencies[$this->Isotope->Config->currency] . '">
<input type="hidden" name="amount" value="' . $intTotal . '">

<input type="hidden" name="accepturl" value="' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/complete') . '">
<input type="hidden" name="declineurl" value="' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/failed') . '">

<input type="hidden" name="instantcapture" value="1">
<input type="hidden" name="md5key" value="' . md5($this->arrCurrencies[$this->Isotope->Config->currency] . $intTotal . $objOrder->id . $this->epay_secretkey) . '">
<input type="hidden" name="cardtype" value="0">
<input type="hidden" name="windowstate" value="2">
<input type="hidden" name="use3D" value="1">

<input type="submit" value="' . $GLOBALS['TL_LANG']['ISO']['pay_with_epay'][2] . '" />

</form>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
$(\'payment_form\').submit();
//--><!]]>
</script>';
	}
}

