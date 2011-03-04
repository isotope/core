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
 
 
class PaymentExpercash extends IsotopePayment
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
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);
		
		if ($this->validateUrlParams($objOrder))
		{
			return true;
		}
		
		$this->redirect($this->addToUrl('step=failed', true));
	}
	
	
	/**
	 * Process PayPal Instant Payment Notifications (IPN)
	 *
	 * @access public
	 * @return void
	 */
	public function processPostSale() 
	{
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);
		
		if ($this->validateUrlParams($objOrder))
		{
			$objOrder->date_payed = time();
			
			if (ISO_VERSION > 0.2)
			{
				$objOrder->checkout();
			}
			
			$objOrder->save();
			
			$this->log('ExperCash: data accepted', __METHOD__, TL_GENERAL);
		}
		else
		{
			$this->log('ExperCash: data rejected' . print_r($_POST, true), __METHOD__, TL_GENERAL);
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
		
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);
		
		$arrData = array
		(
			'popupId'			=> $this->expercash_popupId,
			'jobId'				=> microtime(),
			'functionId'		=> (FE_USER_LOGGED_IN ? $this->User->id : $this->Isotope->Cart->session),
			'transactionId'		=> $objOrder->order_id,
			'amount'			=> (round($this->Isotope->Cart->grandTotal, 2)*100),
			'currency'			=> $this->Isotope->Config->currency,
			'paymentMethod'		=> 'automatic_payment_method',
			'returnUrl'			=> $this->Environment->base . $this->addToUrl('step=complete', true),
			'errorUrl'			=> $this->Environment->base . $this->addToUrl('step=failed', true),
			'notifyUrl'			=> $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id,
			'profile'			=> $this->expercash_profile,
		);
		
		$strKey = '';
		$strUrl = 'https://epi.expercash.net/epi_popup2.php?';
		
		foreach( $arrData as $k => $v )
		{
			$strKey .= $v;
			$strUrl .= $k . '=' . urlencode($v) . '&amp;';
		}
		
		if (is_file(TL_ROOT . '/' . $this->expercash_css))
		{
			$strUrl .= 'cssUrl=' . urlencode($this->Environment->base . $this->expercash_css) . '&amp;';
		}
		
		$strUrl .= 'language=' . strtoupper($GLOBALS['TL_LANGUAGE']) . '&amp;popupKey=' . md5($strKey.$this->expercash_popupKey);
		
		$strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['ISO']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['ISO']['pay_with_redirect'][1] . '</p>

<iframe src="' . $strUrl . '" width="100%" height="500">
  <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
  Sie können die eingebettete Seite über den folgenden Verweis
  aufrufen: <a href="' . $strUrl . '">ExperCash</a></p>
</iframe>';
	
		return $strBuffer;
	}
	
	
	private function validateUrlParams($objOrder)
	{
		$strKey = md5($this->Input->get('amount') . $this->Input->get('currency') . $this->Input->get('paymentMethod') . $this->Input->get('transactionId') . $this->Input->get('GuTID') . $this->expercash_popupKey);
		
		if ($this->Input->get('exportKey') == $strKey
			&& $this->Input->get('amount') == (round($this->Isotope->Cart->grandTotal, 2)*100)
			&& $this->Input->get('currency') == $this->Isotope->Config->currency
			&& $this->Input->get('transactionId') == $objOrder->order_id)
		{
			return true;
		}
		
		return false;
	}
}

