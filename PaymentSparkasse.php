<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */
 
 
class PaymentSparkasse extends IsotopePayment
{

	/**
	 * processPayment function.
	 * 
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);
		
	}
	
	
	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale() 
	{
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);
		
//		'redirecturlf'			=> $this->Environment->base.$this->addToUrl('step=failed', true),

		echo 'redirecturls='.$this->Environment->base.$this->addToUrl('step=complete', true);
		exit;
	}
	
	
	/**
	 * Return the payment form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);

		$arrUrl = array();
		$strUrl = 'https://' . ($this->debug ? 'test' : '') . 'system.sparkassen-internetkasse.de/vbv/mpi_legacy?';
		
		$arrParam = array
		(
			'amount'				=> number_format($this->Isotope->Cart->grandTotal, 2, ',', ''),
			'basketid'				=> $this->Isotope->Cart->id,
			'command'				=> 'sslform',
			'currency'				=> $this->Isotope->Config->currency,
//			'customer_addr_city'	=> urlencode($this->Isotope->Cart->billingAddress['city']),
//			'customer_addr_street'	=> urlencode($this->Isotope->Cart->billingAddress['street_1']),
//			'customer_addr_zip'		=> urlencode($this->Isotope->Cart->billingAddress['postal']),
//			'deliverycountry'		=> urlencode($this->Isotope->Cart->billingAddress['country']),
			'locale'				=> $GLOBALS['TL_LANGUAGE'],
			'orderid'				=> $objOrder->id,
			'paymentmethod'			=> $this->sparkasse_paymentmethod,
			'sessionid'				=> 's105731611',
			'sslmerchant'			=> $this->sparkasse_sslmerchant,
			'transactiontype'		=> ($this->trans_type == 'auth' ? 'preauthorization' : 'authorization'),
			'version'				=> '1.5',
		);
		
//		ksort($arrParam);
		$arrParam['mac'] = hash_hmac('sha1', implode('', $arrParam), $this->sparkasse_sslpassword);
		
		foreach( $arrParam as $k => $v )
		{
			$arrUrl[] = $k . '=' . $v;
		}
		
		$strUrl .= implode('&', $arrUrl);
		
		return "
<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
window.location.href = '" . $strUrl . "';
//--><!]]>
</script>
<h3>" . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . "</h3>
<p>" . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . "</p>
<p><a href=\"" . $strUrl . "\">" . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2] . "</a>";
	}
}

