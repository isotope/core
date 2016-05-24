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

        // Validate payment status
        switch ($this->getRequestData('STATUS')) {
            case 9:  // Zahlung beantragt (Authorize & Capture)
                $objOrder->date_paid = time();
            // no break
            
            case 5:  // Genehmigt (Authorize ohne Capture)
                $intStatus = $this->new_order_status;
                break;

            case 41: // Unbekannter Wartezustand
            case 51: // Genehmigung im Wartezustand
            case 91: // Zahlung im Wartezustand
            case 52: // Genehmigung nicht bekannt
            case 92: // Zahlung unsicher
            $objConfig = Database::getInstance()->prepare('SELECT orderstatus_error FROM tl_iso_config WHERE id=?')->execute($objOrder->config_id);
                if (!$objConfig->numRows) {
                    $this->log('Config for Order ID ' . $objOrder->id . ' not found', __METHOD__, TL_ERROR);
                    return;
                }
                $intStatus = $objConfig->orderstatus_error;
                break;

            case 0:  // Ungültig / Unvollständig
            case 1:  // Zahlungsvorgang abgebrochen
            case 2:  // Genehmigung verweigert
            case 4:  // Gespeichert
            case 93: // Bezahlung verweigert
            default:
                return;
        }

        if (!$objOrder->checkout()) {
            $this->log('Post-Sale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
            return;
        }

		$objOrder->updateOrderStatus($intStatus);

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
		$strFailedUrl = $this->Environment->base . $this->addToUrl('step=failed', true);

		$arrParam = array
		(
			'PSPID'			=> $this->postfinance_pspid,
			'ORDERID'		=> $objOrder->id,
			'AMOUNT'		=> round(($this->Isotope->Cart->grandTotal * 100)),
			'CURRENCY'		=> $this->Isotope->Config->currency,
			'LANGUAGE'		=> $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
			'CN'			=> $objAddress->firstname . ' ' . $objAddress->lastname,
			'EMAIL'			=> $objAddress->email,
			'OWNERZIP'		=> $objAddress->postal,
			'OWNERADDRESS'	=> $objAddress->street_1,
			'OWNERADDRESS2'	=> $objAddress->street_2,
			'OWNERCTY'		=> $objAddress->country,
			'OWNERTOWN'		=> $objAddress->city,
			'OWNERTELNO'	=> $objAddress->phone,
			'ACCEPTURL'		=> $this->Environment->base . IsotopeFrontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->addToUrl('step=complete', true)),
			'DECLINEURL'	=> $strFailedUrl,
			'EXCEPTIONURL'	=> $strFailedUrl,
			'PARAMPLUS'		=> 'mod=pay&amp;id=' . $this->id,
		);

		// SHA-1 must be generated on alphabetically sorted keys.
		ksort($arrParam);

		$strSHASign = '';
		foreach( $arrParam as $k => $v )
		{
			if ($v == '')
				continue;

			$strSHASign .= $k . '=' . htmlspecialchars_decode($v) . $this->postfinance_secret;
		}

		$arrParam['SHASIGN'] = sha1($strSHASign);

		$objTemplate = new FrontendTemplate('iso_payment_postfinance');

		$objTemplate->action = 'https://e-payment.postfinance.ch/ncol/' . ($this->debug ? 'test' : 'prod') . '/orderstandard_utf8.asp';
		$objTemplate->params = $arrParam;
		$objTemplate->slabel = $GLOBALS['TL_LANG']['MSC']['pay_with_cc'][2];
		$objTemplate->id = $this->id;

		return $objTemplate->parse();
	}


	private function getRequestData($strKey)
	{
		if ($this->postfinance_method == 'GET')
		{
			return $this->Input->get($strKey, true);
		}

		return $this->Input->post($strKey, true);
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

        uksort($arrParam, 'strnatcasecmp');

		foreach( $arrParam as $k => $v )
		{
			if ($v == '')
				continue;

			$strSHASign .= strtoupper($k) . '=' . $v . $this->postfinance_secret;
		}

        $strHash = strtoupper(sha1($strSHASign));

        if ($this->getRequestData('SHASIGN') == $strHash)
		{
			return true;
		}

        log_message(
            "Received invalid Postfinance postsale data:
Calculated hash ($strHash) does not match input value ({$this->getRequestData('SHASIGN')})
URL: " . \Environment::getInstance()->url . \Environment::getInstance()->request . "
POST Data: ". print_r($_POST, true),
            'postfinance.log');

		return false;
	}
}

