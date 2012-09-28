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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Handle Postfinance (swiss post) payments
 *
 * @extends Payment
 */
class PaymentPostfinance extends IsotopePayment
{

	/**
	 * Process payment on confirmation page.
	 *
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		if ($this->Input->get('NCERROR') > 0)
		{
			$this->log('Order ID "' . $this->Input->get('orderID') . '" has NCERROR ' . $this->Input->get('NCERROR'), __METHOD__, TL_ERROR);
			return false;
		}

		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $this->Input->get('orderID')))
		{
			$this->log('Order ID "' . $this->Input->get('orderID') . '" not found', __METHOD__, TL_ERROR);
			return false;
		}

		$this->postfinance_method = 'GET';

		if (!$this->validateSHASign())
		{
			$this->log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);
			return false;
		}

		// Validate payment data (see #2221)
		if ($objOrder->currency != $this->getRequestData('currency') || $objOrder->grandTotal != $this->getRequestData('amount'))
		{
			$this->log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}

		$objOrder->date_paid = time();
		$objOrder->save();

		return true;
	}


	/**
	 * Process post-sale requestion from the Postfinance payment server.
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale()
	{
		if ($this->getRequestData('NCERROR') > 0)
		{
			$this->log('Order ID "' . $this->getRequestData('orderID') . '" has NCERROR ' . $this->getRequestData('NCERROR'), __METHOD__, TL_ERROR);
			return;
		}

		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $this->getRequestData('orderID')))
		{
			$this->log('Order ID "' . $this->getRequestData('orderID') . '" not found', __METHOD__, TL_ERROR);
			return;
		}

		if (!$this->validateSHASign())
		{
			$this->log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);
			return;
		}

		// Validate payment data (see #2221)
		if ($objOrder->currency != $this->getRequestData('currency') || $objOrder->grandTotal != $this->getRequestData('amount'))
		{
			$this->log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);
			return;
		}

		if (!$objOrder->checkout())
		{
			$this->log('Post-Sale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
			return;
		}

		$objOrder->date_paid = time();
		$objOrder->save();
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

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->redirect($this->addToUrl('step=failed', true));
		}

		$arrAddress = $this->Isotope->Cart->billingAddress;
		$strFailedUrl = $this->Environment->base . $this->addToUrl('step=failed');

		$arrParam = array
		(
			'PSPID'			=> $this->postfinance_pspid,
			'orderID'		=> $objOrder->id,
			'amount'		=> round(($this->Isotope->Cart->grandTotal * 100)),
			'currency'		=> $this->Isotope->Config->currency,
			'language'		=> $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
			'CN'			=> $arrAddress['firstname'] . ' ' . $arrAddress['lastname'],
			'EMAIL'			=> $arrAddress['email'],
			'ownerZIP'		=> $arrAddress['postal'],
			'owneraddress'	=> $arrAddress['street_1'],
			'owneraddress2'	=> $arrAddress['street_2'],
			'ownercty'		=> $arrAddress['country'],
			'ownertown'		=> $arrAddress['city'],
			'ownertelno'	=> $arrAddress['phone'],
			'accepturl'		=> $this->Environment->base . $this->addToUrl('step=complete') . '?uid=' . $objOrder->uniqid,
			'declineurl'	=> $strFailedUrl,
			'exceptionurl'	=> $strFailedUrl,
			'paramplus'		=> 'mod=pay&id=' . $this->id,
		);

		// SHA-1 must be generated on alphabetically sorted keys. Cant use ksort because it does not ignore key case.
		uksort($arrParam, 'strcasecmp');

		$strSHASign = '';
		foreach( $arrParam as $k => $v )
		{
			if ($v == '')
				continue;

			$strSHASign .= strtoupper($k) . '=' . $v . $this->postfinance_secret;
		}

		$arrParam['SHASign'] = sha1($strSHASign);

		$objTemplate = new FrontendTemplate('iso_payment_postfinance');

		$objTemplate->action = 'https://e-payment.postfinance.ch/ncol/' . ($this->debug ? 'test' : 'prod') . '/orderstandard.asp';
		$objTemplate->params = $arrParam;
		$objTemplate->slabel = $GLOBALS['TL_LANG']['MSC']['pay_with_cc'][2];
		$objTemplate->id = $this->id;

		return $objTemplate->parse();
	}


	private function getRequestData($strKey)
	{
		if ($this->postfinance_method == 'GET')
			return $this->Input->get($strKey);

		return $this->Input->post($strKey);
	}


	/**
	 * Validate SHA-OUT signature
	 */
	private function validateSHASign()
	{
		$strSHASign = '';
		$arrParam = array();
		$arrSHAOut = array('AAVADDRESS', 'AAVCHECK', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT', 'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CREATION_STATUS', 'CURRENCY', 'CVCCHECK', 'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE', 'DCC_EXCHRATETS', 'DCC_INDICATOR', 'DCC_MARGINPERC', 'ENTAGE', 'DCC_VALIDHOURS', 'DIGESTC', 'ARDNO', 'ECI', 'ED', 'ENCCARDNO', 'IP', 'IPCTY', 'NBREMAILUSAGE', 'NBRIPUSAGE', 'NBRIPUSAGE_ALLTX', 'NBRUSAGE', 'NCERROR', 'ORDERID', 'PAYID', 'PM', 'STATUS', 'SUBBRAND', 'TRXDATE', 'VC');

		foreach( array_keys(($this->postfinance_method == 'GET' ? $_GET : $_POST)) as $key )
		{
			if (in_array(strtoupper($key), $arrSHAOut))
			{
				$arrParam[$key] = $this->getRequestData($key);
			}
		}

		uksort($arrParam, 'strcasecmp');

		foreach( $arrParam as $k => $v )
		{
			if ($v == '')
				continue;

			$strSHASign .= strtoupper($k) . '=' . $v . $this->postfinance_secret;
		}

		if ($this->getRequestData('SHASIGN') == strtoupper(sha1($strSHASign)))
		{
			return true;
		}

		return false;
	}
}

