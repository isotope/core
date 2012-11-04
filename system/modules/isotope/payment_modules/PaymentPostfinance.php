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

namespace Isotope;


/**
 * Class PaymentPostfinance
 *
 * Handle Postfinance (swiss post) payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
		if (\Input::get('NCERROR') > 0)
		{
			$this->log('Order ID "' . \Input::get('orderID') . '" has NCERROR ' . \Input::get('NCERROR'), __METHOD__, TL_ERROR);
			return false;
		}

		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', \Input::get('orderID')))
		{
			$this->log('Order ID "' . \Input::get('orderID') . '" not found', __METHOD__, TL_ERROR);
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

		$objAddress = $this->Isotope->Cart->billingAddress;
		$strFailedUrl = $this->Environment->base . $this->addToUrl('step=failed');

		$arrParam = array
		(
			'PSPID'			=> $this->postfinance_pspid,
			'orderID'		=> $objOrder->id,
			'amount'		=> round(($this->Isotope->Cart->grandTotal * 100)),
			'currency'		=> $this->Isotope->Config->currency,
			'language'		=> $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
			'CN'			=> $objAddress->firstname . ' ' . $objAddress->lastname,
			'EMAIL'			=> $objAddress->email,
			'ownerZIP'		=> $objAddress->postal,
			'owneraddress'	=> $objAddress->street_1,
			'owneraddress2'	=> $objAddress->street_2,
			'ownercty'		=> $objAddress->country,
			'ownertown'		=> $objAddress->city,
			'ownertelno'	=> $objAddress->phone,
			'accepturl'		=> $this->Environment->base . IsotopeFrontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->addToUrl('step=complete')),
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
			return \Input::get($strKey);

		return \Input::post($strKey);
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

