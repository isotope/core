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
 * @copyright  iBROWs Web Communications GmbH 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class PaymentSaferpay extends IsotopePayment
{
	
	/**
	 * The hosting gateway URL to create payinit URL 
	 * @var string
	 */
	protected $strInitUrl = 'https://www.saferpay.com/hosting/CreatePayInit.asp';
	
	/**
	 * The hosting gateway URL to VerifyPayConfirm: Check the returned paramter, avoid manipulation
	 * @var string
	 */
	protected $strConfirmUrl = 'https://www.saferpay.com/hosting/VerifyPayConfirm.asp';
	
	
	/**
	 * The hosting gateway URL to PayComplete 
	 * @var string
	 */
	protected $strCaptureUrl = 'https://www.saferpay.com/hosting/PayComplete.asp';
	
	
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'available':
				if (!function_exists('simplexml_load_string'))
				{
					$this->log('PHP SimpleXML is required to use Saferpay payment gateway', 'PaymentSaferpay __get', TL_ERROR);
					return false;
				}
					
				return parent::__get($strKey);
				
			default:
				return parent::__get($strKey);
		}
	}
	
	
	/**
	 * Process checkout payment.
	 * 
	 * @access public
	 * @return mixed
	 */
	public function processPayment()
	{
		// Cannot use $this->Input->get() here because it would kill XML data
		$strData = $_GET['DATA'];
		
		// catch magic_quotes_gpc is set to yes in PHP.ini
		if (substr($strData, 0, 15) == "<IDP MSGTYPE=\\\"")
		{
			$strData = stripslashes($strData);
		}
		
		// Get the Payment URL from the saferpay hosting server
		$objRequest = new Request();
		$objRequest->send($this->strConfirmUrl . "?DATA=" . urlencode($strData) . "&SIGNATURE=" . urlencode($this->Input->get('SIGNATURE'))); 
		
		// Stop if verification is not working
		if (strtoupper(substr($objRequest->response, 0, 3)) != "OK:")
		{
			global $objPage;
			$this->log('Payment not successfull', 'PaymentSaferpay verifyPayment()', TL_ERROR);
			var_dump($objRequest->response);
			exit;
			$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/failed'));
		}
	
		// Used values in start.php (you should do it dynamically, of course)
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Isotope->Cart->id);
	
		$arrXML = new SimpleXMLElement($strData);

		if( $arrXML["ACCOUNTID"] != $this->saferpay_accountid ||
			$arrXML["AMOUNT"] != (round($this->Isotope->Cart->grandTotal, 2) * 100) ||
			$arrXML["CURRENCY"] != $this->Isotope->Config->currency ||
			$arrXML["ORDERID"] != $objOrder->id )
		{
			global $objPage;
			$this->log('XML data wrong, possible manipulation!', 'PaymentSaferpay verifyPayment()', TL_ERROR);
			$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/failed'));
		}
		
		return $this->trans_type == 'auth' ? true : $this->capturePayment($objRequest->response);
	}
	
	
	/**
	 * HTML form for checkout
	 * 
	 * @access public
	 * @return mixed
	 */
	public function checkoutForm()
	{
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Isotope->Cart->id);
		
		$strComplete = $this->Environment->base . $this->addToUrl('step=complete');
		$strFailed = $this->Environment->base . $this->addToUrl('step=failed');
		
		// Mandatory attributes
		$strUrl  = $this->strInitUrl;
		$strUrl .= "?ACCOUNTID=" . $this->saferpay_accountid;
		$strUrl .= "&AMOUNT=" . (round($this->Isotope->Cart->grandTotal, 2) * 100);
		$strUrl .= "&CURRENCY=" . $this->Isotope->Config->currency;
		$strUrl .= "&DESCRIPTION=" . urlencode($this->saferpay_description);
		$strUrl .= "&SUCCESSLINK=" . urlencode($strComplete);
		$strUrl .= "&FAILLINK=" . urlencode($strFailed);
		$strUrl .= "&BACKLINK=" . urlencode($strFailed);
		
		// Additional attributes
		$strUrl .= "&CCCVC=yes"; // input of cardsecuritynumber mandatory
		$strUrl .= "&CCNAME=yes"; // input of cardholder name mandatory
		
		// Important (but optional) attributes
		$strUrl .= "&ORDERID=" . $objOrder->id; // order id
		
		// Get redirect url
		$objRequest = new Request();
		$objRequest->send($strUrl);
		
		if ($objRequest->code != 200)
		{
			global $objPage;
			$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/failed'));
		}
		
		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="0; URL=' . $objRequest->response . '">';
		
		return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_saferpay'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_saferpay'][1] . '</p>
<p><a href="' . $objRequest->response . '">' . $GLOBALS['TL_LANG']['MSC']['pay_with_saferpay'][2]. '</a></p>';
	}
	
	
	/**
	 * Return a list of valid credit card types for this payment module
	 */
	public function getAllowedCCTypes()
	{
		return array();
	}
	
	
	/**
	 * Capture payment after successful verification
	 *
	 * @return bool
	 */
	private function capturePayment($strResponse)
	{
		// Parse ID and TOKEN out of $verification from Saferpay-Call VerifyPayConfirm
		$arrResponse = array();
		parse_str(substr($strResponse, 3), $arrResponse);
	
		// Put all attributes together and create hosting PayComplete URL 
		// For hosting: each attribute which could have non-url-conform characters inside should be urlencoded before
		$strUrl  = $this->strCaptureUrl . "?ACCOUNTID=" . $this->saferpay_accountid; 
		$strUrl .= "&ID=" . urlencode($arrResponse['ID']) . "&TOKEN=" . urlencode($arrResponse['TOKEN']);

		// Special for testaccount: Passwort for hosting-capture neccessary.
		// Not needed for standard-saferpay-eCommerce-accounts
		if( substr(	$this->saferpay_accountid, 0, 6) == "99867-" )
		{
			$strUrl .= "&spPassword=XAjc3Kna";
		}

		// Call the Capture URL from the saferpay hosting server 
		$objRequest = new Request();
		$objRequest->send($strUrl);

		// Stop if capture is not successful
		if (strtoupper($objRequest->response) != "OK")
		{
			$this->log('Payment capture failed', 'PaymentSaferpay verifyPayment()', TL_ERROR);
			return false;
		}
	
		return true;
	}
}

