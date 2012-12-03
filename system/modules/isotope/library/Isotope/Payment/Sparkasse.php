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

namespace Isotope\Payment;

use \Isotope\Collection\Order;


/**
 * Class Sparkasse
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 */
class Sparkasse extends Payment
{

	/**
	 * processPayment function.
	 *
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		return true;
	}


	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale()
	{
		// Sparkasse system sent error message
		if (\Input::post('directPosErrorCode') > 0)
		{
			$this->postsaleFailed(\Input::post('directPosErrorMessage'));
		}

		echo 'redirecturls='.$this->Environment->base . $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)\Input::post('sessionid'))->fetchAssoc(), '/step/complete');
		exit;
	}


	private function postsaleFailed($strReason='')
	{
		echo 'redirecturlf='.$this->Environment->base . $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)\Input::post('sessionid'))->fetchAssoc(), '/step/failed') . ($strReason != '' ? '?reason='.$strReason : '');
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
		global $objPage;

		if (($objOrder = Order::findOneBy('cart_id', $this->Isotope->Cart->id)) === null)
		{
    		$this->redirect($this->addToUrl('step=failed', true));
		}


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
			'sessionid'				=> $objPage->id,
			'sslmerchant'			=> $this->sparkasse_sslmerchant,
			'transactiontype'		=> ($this->trans_type == 'auth' ? 'preauthorization' : 'authorization'),
			'version'				=> '1.5',
		);

		if ($this->sparkasse_merchantref != '')
		{
			$arrParam['merchantref'] = substr($this->replaceInsertTags($this->sparkasse_merchantref), 0, 30);
		}

		ksort($arrParam);

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

