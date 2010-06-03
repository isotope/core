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
				if (!in_array($this->Isotope->Config->currency, $this->arrCurrencies))
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
		return true;
/*
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Isotope->Cart->id);
		
		$arrData = deserialize($objOrder->payment_data, true);
		
		if (strlen($arrData['status']) && $arrData['status'] == 'Completed')
		{
			unset($_SESSION['PAYPAL_TIMEOUT']);
			return true;
		}
		
		if (!isset($_SESSION['PAYPAL_TIMEOUT']))
		{
			$_SESSION['PAYPAL_TIMEOUT'] = 60;
		}
		else
		{
			$_SESSION['PAYPAL_TIMEOUT'] = $_SESSION['PAYPAL_TIMEOUT'] - 5;
		}
		
		if ($_SESSION['PAYPAL_TIMEOUT'] === 0)
		{
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'error';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['paypal_processing_failed'];
			return $objTemplate->parse();
		}
		
		// Reload page every 5 seconds and check if payment was successful
		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="5,' . $this->Environment->base . $this->Environment->request . '">';
		
		$objTemplate = new FrontendTemplate('mod_message');
		$objTemplate->type = 'processing';
		$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['paypal_processing'];
		return $objTemplate->parse();
*/
	}
	
	
	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale() 
	{
/*
		$arrData = array();
		foreach( $_POST as $k => $v )
		{
			$arrData[] = $k . '=' . $v;
		}

		$objRequest = new Request();
		$objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate'), implode('&', $arrData), 'post');
		
		if ($objRequest->response == 'VERIFIED' && $this->Input->post('receiver_email') == $this->paypal_account)
		{
			$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE order_id=?")->limit(1)->execute($this->Input->post('invoice'));
		
			if (!$objOrder->numRows)
			{
				$this->log('Order ID "' . $this->Input->post('invoice') . '" not found', 'PaymentPaypal processPostSale()', TL_ERROR);
				return;
			}

			// Set the current system to the language when the user placed the order.
			// This will result in correct e-mails and payment description.
			$GLOBALS['TL_LANGUAGE'] = $objOrder->language;
			$this->loadLanguageFile('default');
			
			// Load / initialize data
			$arrPayment = deserialize($objOrder->payment_data, true);
			
			// Store request data in order for future references
			$arrPayment['POSTSALE'][] = $_POST;
			
			
			$arrData = $objOrder->row();
			$arrData['old_payment_status'] = $arrPayment['status'];
			
			$arrPayment['status'] = $this->Input->post('payment_status');
			$arrData['new_payment_status'] = $arrPayment['status'];
			
			// array('pending','processing','complete','on_hold', 'cancelled'),
			switch( $arrPayment['status'] )
			{
				case 'Completed':
					$this->Database->execute("UPDATE tl_iso_orders SET date_payed=" . time() . " WHERE id=" . $objOrder->id);
					break;
					
				case 'Canceled_Reversal':
				case 'Denied':
				case 'Expired':
				case 'Failed':
				case 'Voided':
					$this->Database->execute("UPDATE tl_iso_orders SET date_payed='' WHERE id=" . $objOrder->id);
					$this->Database->execute("UPDATE tl_iso_orders SET status='on_hold' WHERE status='complete' AND id=" . $objOrder->id);
					break;
					
				case 'In-Progress':
				case 'Partially_Refunded':
				case 'Pending':
				case 'Processed':
				case 'Refunded':
				case 'Reversed':
					break;
			}
			
			$this->Database->prepare("UPDATE tl_iso_orders SET payment_data=? WHERE id=?")->execute(serialize($arrPayment), $objOrder->id);
			
			if ($this->postsale_mail)
			{
				$this->Import('Isotope');
				$this->Isotope->overrideConfig($objOrder->config_id);
				$this->Isotope->sendMail($this->postsale_mail, $GLOBALS['TL_ADMIN_EMAIL'], $GLOBALS['TL_LANGUAGE'], $arrData);
			}
			
			$this->log('PayPal IPN: data accepted ' . print_r($_POST, true), 'PaymentPaypal processPostSale()', TL_GENERAL);
		}
		else
		{
			$this->log('PayPal IPN: data rejected (' . $objRequest->response . ') ' . print_r($_POST, true), 'PaymentPaypal processPostSale()', TL_GENERAL);
		}
		
		header('HTTP/1.1 200 OK');
		exit;
*/
	}
	
	
	/**
	 * Return the PayPal form.
	 * 
	 * @access public
	 * @return string
	 */
	public function checkoutForm()
	{
		$this->import('Isotope');
		
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Isotope->Cart->id);
		
		return '
<h2>' . $GLOBALS['TL_LANG']['ISO']['pay_with_epay'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['ISO']['pay_with_epay'][1] . '</p>
<form id="payment_form" action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post">

<input type="hidden" name="language" value="' . (in_array($GLOBALS['TL_LANGUAGE'], $this->arrLanguages) ? $this->arrLanguages[$GLOBALS['TL_LANGUAGE']] : 2) . '">
<input type="hidden" name="merchantnumber" value="' . $this->epay_merchantnumber . '">
<input type="hidden" name="orderid" value="' . $objOrder->order_id . '">
<input type="hidden" name="currency" value="' . $this->arrLanguages[$GLOBALS['TL_LANGUAGE']] . '">
<input type="hidden" name="amount" value="' . $this->Isotope->Cart->grandTotal . '">

<input type="hidden" name="accepturl" value="' . $this->Environment->base . $this->addToUrl('step=complete') . '">
<input type="hidden" name="declineurl" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '">
<input type="hidden" name="callbackurl" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '">

<input type="hidden" name="instantcapture" value="">
<input type="hidden" name="ordretext" value="">
<input type="hidden" name="md5key" value="">
<input type="hidden" name="cardtype" value="0">
<input type="hidden" name="windowstate" value="2">
<input type="hidden" name="use3D" value="1">

</form>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
$(\'payment_form\').submit();
//--><!]]>
</script>';
	}
}

