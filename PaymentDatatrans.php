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
 * @author     Leo Unglaub <leo.unglaub@iserv.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id: $
 */


class PaymentDatatrans extends IsotopePayment
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
	 * Server 2 Server check
	 */
	public function processPostSale()
	{
		$this->import('Input');

		// stop if something went wrong
		if ($this->Input->post('status') != 'success')
		{
			$this->log('Order ID "' . $this->Input->post('refno') . '" has NOT succeedet. UPP Transaction Id: ' . $this->Input->post('uppTransactionId'), __METHOD__, TL_ERROR);
			return;
		}

		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $this->Input->post('refno')))
		{
			$this->log('Order ID "' . $this->Input->post('refno') . '" not found', __METHOD__, TL_ERROR);
			return;
		}

		// check if the details are okay
		if ($this->Input->post('merchantId') == $this->datatrans_id)
		{
			// do the optional sign check
			if ($this->datatrans_sign == 1)
			{
				if ($this->datatrans_sign_value != $this->Input->post('sign'))
				{
					$this->log('Call without a valid sign id', __METHOD__, TL_ERROR);
					return;
				}
			}

			// new in isotope 1.3
			if (version_compare(ISO_VERSION, '0.2', '>'))
			{
				$objOrder->checkout();
			}

			$objOrder->date_payed = time();
			$objOrder->save();

		}
	}


	/**
	 * Check if the server to server check was sucessfull before we tag the order as payed
	 * @return bool
	 */
	public function processPayment()
	{
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->log('Cart ID "' . $this->Isotope->Cart->id . '" not found', __METHOD__, TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}

		if ($objOrder->date_payed > 0)
			return true;

		$this->redirect($this->addToUrl('step=failed', true));
	}


	/**
	 * Generate the submit form for datatrans and if javascript
	 * is enabled redirect automaticly
	 *
	 * @return string
	 */
	public function checkoutForm()
	{
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->redirect($this->addToUrl('step=failed', true));
		}

		$this->loadLanguageFile('tl_iso_payment_modules');
		$arrParams = array
		(
			'merchantId'	=> $objOrder->Payment->datatrans_id,
			'amount'		=> $this->Isotope->Cart->grandTotal,
			'currency'		=> $this->Isotope->Config->currency,
			'refno'			=> $objOrder->id, // Order or transaction ID
			'mod'			=> 'pay',
			'id'			=> $this->id
		);

		// add the security sign
		if ($this->datatrans_sign == 1)
		{
			$arrParams['sign'] = $this->datatrans_sign_value;
		}

		$objTemplate = new FrontendTemplate('iso_payment_datatrans');
		$objTemplate->params = $arrParams;
		$objTemplate->action = 'https://pilot.datatrans.biz/upp/jsp/upStart.jsp'; // Live URL: https://payment.datatrans.biz/upp/jsp/upStart.jsp
		$objTemplate->slabel = $GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_label_pay'];
		$objTemplate->id = $this->id;

		return $objTemplate->parse();
	}
}


?>