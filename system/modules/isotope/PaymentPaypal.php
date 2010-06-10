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
class PaymentPaypal extends IsotopePayment
{

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
		$this->import('IsotopeCart', 'Cart');
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE cart_id=? AND status!='cancelled'")->limit(1)->execute($this->Cart->id);
		
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
	}
	
	
	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale() 
	{
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
		$this->import('IsotopeCart', 'Cart');
		
		$objOrder = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE cart_id=?")->execute($this->Cart->id);
		
		return '
<h2>' . $GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][1] . '</p>
<form id="payment_form" action="https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="charset" value="UTF-8">
<input type="hidden" name="business" value="' . $this->paypal_account . '">
<input type="hidden" name="lc" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '">
<input type="hidden" name="item_name" value="' . $this->paypal_business . '"/>
<input type="hidden" name="amount" value="' . round($this->Cart->grandTotal, 2) . '"/>
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="' . $this->Isotope->Config->currency . '">
<input type="hidden" name="button_subtype" value="services">
<input type="hidden" name="return" value="' . $this->Environment->base . $this->addToUrl('step=complete') . '">
<input type="hidden" name="cancel_return" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '">
<input type="hidden" name="rm" value="1">
<input type="hidden" name="invoice" value="' . $objOrder->order_id . '">

<input type="hidden" name="address_override" value="1">
<input type="hidden" name="first_name" value="' . $this->Cart->billingAddress['firstname'] . '">
<input type="hidden" name="last_name" value="' . $this->Cart->billingAddress['lastname'] . '">
<input type="hidden" name="address1" value="' . $this->Cart->billingAddress['street_1'] . '">
<input type="hidden" name="address2" value="' . $this->Cart->billingAddress['street_2'] . '">
<input type="hidden" name="zip" value="' . $this->Cart->billingAddress['postal'] . '">
<input type="hidden" name="city" value="' . $this->Cart->billingAddress['city'] . '">
<input type="hidden" name="country" value="' . strtoupper($this->Cart->billingAddress['country']) . '">
<input type="hidden" name="email" value="' . $this->Cart->billingAddress['email'] . '">
<input type="hidden" name="night_phone_c" value="' . $this->Cart->billingAddress['phone'] . '">

<input type="hidden" name="notify_url" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
<input type="' . (strlen($this->button) ? 'image" src="'.$this->button.'" border="0"' : 'submit" value="'.specialchars($GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][2]).'"') . ' alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
$(\'payment_form\').submit();
//--><!]]>
</script>';
	}
}

