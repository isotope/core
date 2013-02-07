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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class PaymentSparkasse extends IsotopePayment
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
			// Do not index or cache the page
			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'processing';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
			return $objTemplate->parse();
		}

		$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);

		$this->redirect($this->addToUrl('step=failed', true));
    }


    /**
     * Server to server communication
     *
     * @access public
     * @return void
     */
    public function processPostSale()
    {
        $arrData = array();

        foreach (array('aid', 'amount', 'basketid', 'currency', 'directPosErrorCode', 'directPosErrorMessage', 'orderid', 'rc', 'retrefnum', 'sessionid', 'trefnum') as $strKey)
        {
            $arrData[$strKey] = $this->Input->post($strKey);
        }

        // Convert amount, Sparkasse is using comma instead of dot as decimal separator
        $arrData['amount'] = str_replace(',', '.', preg_replace('/[^0-9,]/', '', $arrData['amount']));

        // Sparkasse system sent error message
        if ($arrData['directPosErrorCode'] > 0)
        {
            $this->redirectError($arrData);
        }

        // Check the data hash to prevent manipulations
        if ($this->Input->post('mac') != $this->calculateHash($arrData))
        {
            $this->log('Security hash mismatch in Sparkasse payment!', __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        $objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $arrData['orderid']))
		{
			$this->log('Order ID "' . $arrData['orderid'] . '" not found', __METHOD__, TL_ERROR);
			$this->redirectError($arrData);
		}

		// Validate payment data
		if ($objOrder->currency != $arrData['currency'])
		{
			$this->log(sprintf('Data manipulation: currency mismatch ("%s" != "%s")', $objOrder->currency, $arrdata['currency']), __METHOD__, TL_ERROR);
			$this->redirectError($arrData);
		}
		elseif ($objOrder->grandTotal != $arrData['amount'])
		{
    		$this->log(sprintf('Data manipulation: amount mismatch ("%s" != "%s")', $objOrder->grandTotal, $arrData['amount']), __METHOD__, TL_ERROR);
			$this->redirectError($arrData);
		}

		if (!$objOrder->checkout())
		{
			$this->log('Postsale checkout for order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
			$this->redirectError($arrData);
		}

		// Store request data in order for future references
		$arrPayment = deserialize($objOrder->payment_data, true);
		$arrPayment['POSTSALE'][] = $_POST;
		$objOrder->payment_data = $arrPayment;

		$objOrder->date_paid = time();
		$objOrder->updateOrderStatus($this->new_order_status);

		$objOrder->save();

		$objPage = $this->getPageDetails((int) $arrData['sessionid']);

        echo 'redirecturls=' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/complete', $objPage->language);
        exit;
    }


    /**
     * Return the payment form.
     *
     * @access public
     * @return string
     */
    public function checkoutForm()
    {
        global $objPage;

        $objOrder = new IsotopeOrder();
        $objOrder->findBy('cart_id', $this->Isotope->Cart->id);

        $arrUrl = array();
        $strUrl = 'https://' . ($this->debug ? 'test' : '') . 'system.sparkassen-internetkasse.de/vbv/mpi_legacy?';

        $arrParam = array
        (
            'amount'                    => number_format($this->Isotope->Cart->grandTotal, 2, ',', ''),
            'basketid'                  => $this->Isotope->Cart->id,
            'command'                   => 'sslform',
            'currency'                  => $this->Isotope->Config->currency,
            'locale'                    => $GLOBALS['TL_LANGUAGE'],
            'orderid'                   => $objOrder->id,
            'paymentmethod'             => $this->sparkasse_paymentmethod,
            'sessionid'                 => $objPage->id,
            'sslmerchant'               => $this->sparkasse_sslmerchant,
            'transactiontype'           => ($this->trans_type == 'auth' ? 'preauthorization' : 'authorization'),
            'version'                   => '1.5',
        );

        if ($this->sparkasse_merchantref != '')
        {
            $arrParam['merchantref'] = substr($this->replaceInsertTags($this->sparkasse_merchantref), 0, 30);
        }

        $arrParam['mac'] = $this->calculateHash($arrParam);

        foreach( $arrParam as $k => $v )
        {
            $arrUrl[] = $k . '=' . $v;
        }

        $strUrl .= implode('&', $arrUrl);

        return "
<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
window.location.href = '" . $strUrl . "';
//--><!]]>
</script>
<h3>" . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . "</h3>
<p>" . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . "</p>
<p><a href=\"" . $strUrl . "\">" . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2] . "</a>";
    }


    /**
     * Calculate hash
     * @param  array
     * @return string
     */
    private function calculateHash($arrData)
    {
        ksort($arrData);

        return hash_hmac('sha1', implode('', $arrData), $this->sparkasse_sslpassword);
    }


    /**
     * Redirect the Sparkasse server to our error page
     * @param arary
     */
    private function redirectError($arrData)
    {
        $objPage = $this->getPageDetails((int) $arrData['sessionid']);

        echo 'redirecturlf=' . $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/failed', $objPage->language) . '?reason=' . $arrData['directPosErrorMessage'];
        exit;
    }
}

