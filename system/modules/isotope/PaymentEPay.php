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

	protected $arrLanguages = array('da'=>1, 'en'=>2, 'sv'=>3, 'no'=>4, 'kl'=>5, 'is'=>6, 'de'=>7, 'fi'=>8);
	protected $arrCurrencies = array('AUD'=>036, 'CAD'=>124, 'DKK'=>208, 'HKD'=>344, 'ISK'=>352, 'JPY'=>392, 'MXN'=>484, 'NZD'=>554, 'NOK'=>578, 'SGD'=>702, 'ZAR'=>710, 'SEK'=>752, 'CHF'=>756, 'THB'=>764, 'GBP'=>826, 'USD'=>840, 'TRY'=>949, 'EUR'=>978, 'PLN'=>985);
	
	
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
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->executeUncached($this->Isotope->Cart->id);
		$intTotal = $this->Isotope->Cart->grandTotal * 100;
		
		// Check basic order data
		if ($this->Input->get('orderid') == $objOrder->id && $this->Input->get('cur') == $this->arrCurrencies[$this->Isotope->Config->currency] && $this->Input->get('amount') == $intTotal)
		{
			// Validate MD5 secret key
			if (md5($intTotal . $objOrder->id . $this->Input->get('tid') . $this->epay_secretkey) == $this->Input->get('eKey'))
			{
				return true;
			}
		}
		
		global $objPage;
		$this->log('Invalid payment data received.', 'PaymentEPay processPayment()', TL_ERROR);
		$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/failed'));
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
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=?")->limit(1)->executeUncached($this->Isotope->Cart->id);
		$intTotal = round($this->Isotope->Cart->grandTotal, 2) * 100;
		
		return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_epay'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_epay'][1] . '</p>
<form id="payment_form" action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post">

<input type="hidden" name="language" value="' . (array_key_exists($GLOBALS['TL_LANGUAGE'], $this->arrLanguages) ? $this->arrLanguages[$GLOBALS['TL_LANGUAGE']] : 2) . '">
<input type="hidden" name="merchantnumber" value="' . $this->epay_merchantnumber . '">
<input type="hidden" name="orderid" value="' . $objOrder->id . '">
<input type="hidden" name="currency" value="' . $this->arrCurrencies[$this->Isotope->Config->currency] . '">
<input type="hidden" name="amount" value="' . $intTotal . '">

<input type="hidden" name="accepturl" value="' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/complete') . '">
<input type="hidden" name="declineurl" value="' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/failed') . '">

<input type="hidden" name="instantcapture" value="' . ($this->trans_type == 'auth' ? '0' : '1') . '">
<input type="hidden" name="md5key" value="' . md5($this->arrCurrencies[$this->Isotope->Config->currency] . $intTotal . $objOrder->id . $this->epay_secretkey) . '">
<input type="hidden" name="cardtype" value="0">
<input type="hidden" name="windowstate" value="2">
<input type="hidden" name="use3D" value="1">

<input type="submit" class="submit button" value="' . $GLOBALS['TL_LANG']['MSC']['pay_with_epay'][2] . '" />

</form>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent( \'domready\' , function() {
  $(\'payment_form\').submit();
});
//--><!]]>
</script>';
	}
	
	
	/**
	 * Return information or advanced features in the backend.
	 *
	 * @access public
	 * @param  int		Order ID
	 * @return string
	 */
	public function backendInterface($orderId)
	{
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=payment', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['PAY'][$this->type][0] . ')' . '</h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
<h4>' . $GLOBALS['TL_LANG']['MSC']['backendPaymentEPay'] . '</h4>
<p class="tl_info" style="margin-top:5px"><a href="https://ssl.ditonlinebetalingssystem.dk/admin/login.asp"' . LINK_NEW_WINDOW . '>https://ssl.ditonlinebetalingssystem.dk/admin/login.asp</a></p>

</div>
</div>';
	}
}

