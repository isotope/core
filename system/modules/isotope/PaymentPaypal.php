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
		$objOrder = new IsotopeOrder();
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			return false;
		}

		if ($objOrder->date_payed <= time())
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
			global $objPage;
			$this->log('Payment could not be processed.', 'PaymentPaypal processPayment()', TL_ERROR);
			$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/failed'));
		}

		// Reload page every 5 seconds and check if payment was successful
		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="5,' . $this->Environment->base . $this->Environment->request . '">';

		$objTemplate = new FrontendTemplate('mod_message');
		$objTemplate->type = 'processing';
		$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
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

		if ($objRequest->hasError())
		{
			$this->log('Request Error: ' . $objRequest->error, 'PaymentPaypal processPostSale()', TL_ERROR);
			exit;
		}
		elseif ($objRequest->response == 'VERIFIED' && ($this->Input->post('receiver_email') == $this->paypal_account || $this->debug))
		{
			$objOrder = new IsotopeOrder();

			if (!$objOrder->findBy('order_id', $this->Input->post('invoice')))
			{
				$this->log('Order ID "' . $this->Input->post('invoice') . '" not found', 'PaymentPaypal processPostSale()', TL_ERROR);
				return;
			}

			if (!$objOrder->checkout())
			{
				$this->log('IPN checkout for Order ID "' . $this->Input->post('invoice') . '" failed', 'PaymentPaypal processPostSale()', TL_ERROR);
				return;
			}

			// Load / initialize data
			$arrPayment = deserialize($objOrder->payment_data, true);

			// Store request data in order for future references
			$arrPayment['POSTSALE'][] = $_POST;


			$arrData = $objOrder->getData();
			$arrData['old_payment_status'] = $arrPayment['status'];

			$arrPayment['status'] = $this->Input->post('payment_status');
			$arrData['new_payment_status'] = $arrPayment['status'];

			// array('pending','processing','complete','on_hold', 'cancelled'),
			switch( $arrPayment['status'] )
			{
				case 'Completed':
					$objOrder->date_payed = time();
					break;

				case 'Canceled_Reversal':
				case 'Denied':
				case 'Expired':
				case 'Failed':
				case 'Voided':
					$objOrder->date_payed = '';
					if ($objOrder->status == 'complete')
					{
						$objOrder->status = 'on_hold';
					}
					break;

				case 'In-Progress':
				case 'Partially_Refunded':
				case 'Pending':
				case 'Processed':
				case 'Refunded':
				case 'Reversed':
					break;
			}

			$objOrder->payment_data = $arrPayment;

			if ($this->postsale_mail)
			{
				try
				{
					$this->Isotope->overrideConfig($objOrder->config_id);
					$this->Isotope->sendMail($this->postsale_mail, $GLOBALS['TL_CONFIG']['adminEmail'], $GLOBALS['TL_LANGUAGE'], $arrData);
				}
				catch (Exception $e) {}
			}

			$objOrder->save();

			$this->log('PayPal IPN: data accepted', 'PaymentPaypal processPostSale()', TL_GENERAL);
		}
		else
		{
			$this->log('PayPal IPN: data rejected (' . $objRequest->response . ')', 'PaymentPaypal processPostSale()', TL_GENERAL);
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
		$objOrder = new IsotopeOrder();
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->redirect($this->addToUrl('step=failed', true));
		}
		

		$strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<form id="payment_form" action="https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="upload" value="1">
<input type="hidden" name="charset" value="UTF-8">
<input type="hidden" name="business" value="' . $this->paypal_account . '">
<input type="hidden" name="lc" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '">';

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

			$strBuffer .= '
<input type="hidden" name="item_number_'.++$i.'" value="' . $objProduct->sku . '" />
<input type="hidden" name="item_name_'.$i.'" value="' . $objProduct->name . $strOptions . '"/>
<input type="hidden" name="amount_'.$i.'" value="' . $objProduct->price . '"/>
<input type="hidden" name="quantity_'.$i.'" value="' . $objProduct->quantity_requested . '"/>';
		}

		foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge )
		{
			if ($arrSurcharge['add'] === false)
				continue;

			$strBuffer .= '
<input type="hidden" name="item_name_'.++$i.'" value="' . $arrSurcharge['label'] . '"/>
<input type="hidden" name="amount_'.$i.'" value="' . $arrSurcharge['total_price'] . '"/>';
		}

		$strBuffer .= '
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="' . $this->Isotope->Config->currency . '">
<input type="hidden" name="button_subtype" value="services">
<input type="hidden" name="return" value="' . $this->Environment->base . $this->addToUrl('step=complete') . '?uid=' . $objOrder->uniqid . '">
<input type="hidden" name="cancel_return" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '">
<input type="hidden" name="rm" value="1">
<input type="hidden" name="invoice" value="' . $objOrder->order_id . '">

<input type="hidden" name="address_override" value="1">
<input type="hidden" name="first_name" value="' . $this->Isotope->Cart->billingAddress['firstname'] . '">
<input type="hidden" name="last_name" value="' . $this->Isotope->Cart->billingAddress['lastname'] . '">
<input type="hidden" name="address1" value="' . $this->Isotope->Cart->billingAddress['street_1'] . '">
<input type="hidden" name="address2" value="' . $this->Isotope->Cart->billingAddress['street_2'] . '">
<input type="hidden" name="zip" value="' . $this->Isotope->Cart->billingAddress['postal'] . '">
<input type="hidden" name="city" value="' . $this->Isotope->Cart->billingAddress['city'] . '">
<input type="hidden" name="country" value="' . strtoupper($this->Isotope->Cart->billingAddress['country']) . '">
<input type="hidden" name="email" value="' . $this->Isotope->Cart->billingAddress['email'] . '">
<input type="hidden" name="night_phone_b" value="' . $this->Isotope->Cart->billingAddress['phone'] . '">

<input type="hidden" name="notify_url" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
<input type="' . (strlen($this->button) ? 'image" src="'.$this->button.'" border="0"' : 'submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]).'"') . ' alt="PayPal - The safer, easier way to pay online!">
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
}

