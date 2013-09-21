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

namespace Isotope\Model\Payment;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Model\ProductCollection\Order;


/**
 * Class Sparkasse
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 */
class Sparkasse extends Postsale implements IsotopePayment
{

    /**
     * Server to server communication
     *
     * @access public
     * @return void
     */
    public function processPostsale()
    {
        $arrData = array();

        foreach (array('aid', 'amount', 'basketid', 'currency', 'directPosErrorCode', 'directPosErrorMessage', 'orderid', 'rc', 'retrefnum', 'sessionid', 'trefnum') as $strKey)
        {
            $arrData[$strKey] = \Input::post($strKey);
        }

        // Sparkasse system sent error message
        if ($arrData['directPosErrorCode'] > 0)
        {
            $this->redirectError($arrData);
        }

        // Check the data hash to prevent manipulations
        if (\Input::post('mac') != $this->calculateHash($arrData))
        {
            \System::log('Security hash mismatch in Sparkasse payment!', __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('id', $arrData['orderid']))
        {
            \System::log('Order ID "' . $arrData['orderid'] . '" not found', __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        // Convert amount, Sparkasse is using comma instead of dot as decimal separator
        $arrData['amount'] = str_replace(',', '.', preg_replace('/[^0-9,]/', '', $arrData['amount']));

        // Validate payment data
        if ($objOrder->currency != $arrData['currency'])
        {
            \System::log(sprintf('Data manipulation: currency mismatch ("%s" != "%s")', $objOrder->currency, $arrdata['currency']), __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }
        elseif ($objOrder->getTotal() != $arrData['amount'])
        {
            \System::log(sprintf('Data manipulation: amount mismatch ("%s" != "%s")', $objOrder->getTotal(), $arrData['amount']), __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        if (!$objOrder->checkout())
        {
            \System::log('Postsale checkout for order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
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

        echo 'redirecturls=' . \Environment::get('base') . \Controller::generateFrontendUrl($objPage->row(), '/step/complete/uid/' . $objOrder->uniqid, $objPage->language);
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

        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null)
        {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }


        $arrUrl = array();
        $strUrl = 'https://' . ($this->debug ? 'test' : '') . 'system.sparkassen-internetkasse.de/vbv/mpi_legacy?';

        $arrParam = array
        (
            'amount'            => number_format(Isotope::getCart()->getTotal(), 2, ',', ''),
            'basketid'          => Isotope::getCart()->id,
            'command'           => 'sslform',
            'currency'          => Isotope::getConfig()->currency,
            'locale'            => $GLOBALS['TL_LANGUAGE'],
            'orderid'           => $objOrder->id,
            'paymentmethod'     => $this->sparkasse_paymentmethod,
            'sessionid'         => $objPage->id,
            'sslmerchant'       => $this->sparkasse_sslmerchant,
            'transactiontype'   => ($this->trans_type == 'auth' ? 'preauthorization' : 'authorization'),
            'version'           => '1.5',
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

        echo 'redirecturlf=' . \Environment::get('base') . \Controller::generateFrontendUrl($objPage->row(), '/step/failed', $objPage->language) . '?reason=' . $arrData['directPosErrorMessage'];
        exit;
    }
}
