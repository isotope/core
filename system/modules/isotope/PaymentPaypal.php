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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Handle Paypal payments
 */
class PaymentPaypal extends IsotopePayment
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
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			return false;
		}

		if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
		{
			IsotopeFrontend::clearTimeout();
			return true;
		}

		if (IsotopeFrontend::setTimeout())
		{
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'processing';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
			return $objTemplate->parse();
		}

		global $objPage;
		$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
		$this->redirect($this->generateFrontendUrl($objPage->row(), '/step/failed'));
	}


	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale()
	{
		$objRequest = new Request();
		$objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate'), http_build_query($_POST), 'post');

		if ($objRequest->hasError())
		{
			$this->log('Request Error: ' . $objRequest->error, __METHOD__, TL_ERROR);
			exit;
		}
		elseif ($objRequest->response == 'VERIFIED' && ($this->Input->post('receiver_email', true) == $this->paypal_account || $this->debug))
		{
			$objOrder = new IsotopeOrder();

			if (!$objOrder->findBy('id', $this->Input->post('invoice')))
			{
				$this->log('Order ID "' . $this->Input->post('invoice') . '" not found', __METHOD__, TL_ERROR);
				return;
			}
			
			// Validate payment data (see #2221)
			if ($objOrder->currency != $this->Input->post('mc_currency') || $objOrder->grandTotal != $this->Input->post('mc_gross'))
			{
				$this->log('IPN manipulation in payment from "' . $this->Input->post('payer_email') . '" !', __METHOD__, TL_ERROR);
				return;
			}

			if (!$objOrder->checkout())
			{
				$this->log('IPN checkout for Order ID "' . $this->Input->post('invoice') . '" failed', __METHOD__, TL_ERROR);
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
					$objOrder->date_paid = time();
					break;

				case 'Canceled_Reversal':
				case 'Denied':
				case 'Expired':
				case 'Failed':
				case 'Voided':
					$objOrder->date_paid = '';
					$objOrder->status = $this->Isotope->Config->orderstatus_error;
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

			$objOrder->save();

			$this->log('PayPal IPN: data accepted', __METHOD__, TL_GENERAL);
		}
		else
		{
			$this->log('PayPal IPN: data rejected (' . $objRequest->response . ')', __METHOD__, TL_ERROR);
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

		list($endTag, $startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();

		$strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<form id="payment_form" action="https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_cart"' . $endTag . '
<input type="hidden" name="upload" value="1"' . $endTag . '
<input type="hidden" name="charset" value="UTF-8"' . $endTag . '
<input type="hidden" name="business" value="' . $this->paypal_account . '"' . $endTag . '
<input type="hidden" name="lc" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '"' . $endTag;

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
<input type="hidden" name="item_number_'.++$i.'" value="' . $objProduct->sku . '"' . $endTag . '
<input type="hidden" name="item_name_'.$i.'" value="' . $objProduct->name . $strOptions . '"' . $endTag . '
<input type="hidden" name="amount_'.$i.'" value="' . $objProduct->price . '"/>
<input type="hidden" name="quantity_'.$i.'" value="' . $objProduct->quantity_requested . '"' . $endTag;
		}
		
		$fltDiscount = 0;

		foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge )
		{
			if ($arrSurcharge['add'] === false)
				continue;

			// PayPal does only support one single discount item
			if ($arrSurcharge['total_price'] < 0)
			{
				$fltDiscount -= $arrSurcharge['total_price'];
				continue;
			}

			$strBuffer .= '
<input type="hidden" name="item_name_'.++$i.'" value="' . $arrSurcharge['label'] . '"' . $endTag . '
<input type="hidden" name="amount_'.$i.'" value="' . $arrSurcharge['total_price'] . '"' . $endTag;
		}
		
		if ($fltDiscount > 0)
		{
			$strBuffer .= '
<input type="hidden" name="discount_amount_cart" value="' . $fltDiscount . '"' . $endTag;
		}

		$strBuffer .= '
<input type="hidden" name="no_shipping" value="1"' . $endTag . '
<input type="hidden" name="no_note" value="1"' . $endTag . '
<input type="hidden" name="currency_code" value="' . $this->Isotope->Config->currency . '"' . $endTag . '
<input type="hidden" name="button_subtype" value="services"' . $endTag . '
<input type="hidden" name="return" value="' . $this->Environment->base . $this->addToUrl('step=complete') . '?uid=' . $objOrder->uniqid . '"' . $endTag . '
<input type="hidden" name="cancel_return" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '"' . $endTag . '
<input type="hidden" name="rm" value="1"' . $endTag . '
<input type="hidden" name="invoice" value="' . $objOrder->id . '"' . $endTag . '

<input type="hidden" name="address_override" value="' . ($this->debug ? '0' : '1') . '"' . $endTag . '
<input type="hidden" name="first_name" value="' . $this->Isotope->Cart->billingAddress['firstname'] . '"' . $endTag . '
<input type="hidden" name="last_name" value="' . $this->Isotope->Cart->billingAddress['lastname'] . '"' . $endTag . '
<input type="hidden" name="address1" value="' . $this->Isotope->Cart->billingAddress['street_1'] . '"' . $endTag . '
<input type="hidden" name="address2" value="' . $this->Isotope->Cart->billingAddress['street_2'] . '"' . $endTag . '
<input type="hidden" name="zip" value="' . $this->Isotope->Cart->billingAddress['postal'] . '"' . $endTag . '
<input type="hidden" name="city" value="' . $this->Isotope->Cart->billingAddress['city'] . '"' . $endTag . '
<input type="hidden" name="country" value="' . strtoupper($this->Isotope->Cart->billingAddress['country']) . '"' . $endTag . '
<input type="hidden" name="email" value="' . $this->Isotope->Cart->billingAddress['email'] . '"' . $endTag . '
<input type="hidden" name="night_phone_b" value="' . $this->Isotope->Cart->billingAddress['phone'] . '"' . $endTag . '

<input type="hidden" name="notify_url" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '"' . $endTag . '
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted"' . $endTag . '
<input type="' . (strlen($this->button) ? 'image" src="'.$this->button.'" border="0"' : 'submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]).'"') . ' alt="PayPal - The safer, easier way to pay online!"' . $endTag . '
</form>

' . $startScript . '
window.addEvent( \'domready\' , function() {
  $(\'payment_form\').submit();
});
' . $endScript;

		return $strBuffer;
	}
}

