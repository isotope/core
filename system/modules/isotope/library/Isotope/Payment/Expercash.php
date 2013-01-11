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

namespace Isotope\Payment;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Product\Collection\Order;


/**
 * Class Expercash
 *
 * @copyright Isotope eCommerce Workgroup 2009-2012
 * @author    Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Expercash extends Payment implements IsotopePayment
{

    /**
     * processPayment function.
     *
     * @access public
     * @return void
     */
    public function processPayment()
    {
        $objOrder = Order::findOneBy('cart_id', $this->Isotope->Cart->id);

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
        $objOrder = Order::findOneBy('cart_id', $this->Isotope->Cart->id);

        if ($this->validateUrlParams($objOrder))
        {
            $objOrder->date_payed = time();

            if (version_compare(ISO_VERSION, '0.2', '>'))
            {
                $objOrder->checkout();
            }

            $objOrder->save();

            \System::log('ExperCash: data accepted', __METHOD__, TL_GENERAL);
        }
        else
        {
            \System::log('ExperCash: data rejected' . print_r($_POST, true), __METHOD__, TL_GENERAL);
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
        if (($objOrder = Order::findOneBy('cart_id', $this->Isotope->Cart->id)) === null)
        {
            $this->redirect($this->addToUrl('step=failed', true));
        }

        $arrData = array
        (
            'popupId'			=> $this->expercash_popupId,
            'jobId'				=> microtime(),
            'functionId'		=> (FE_USER_LOGGED_IN ? $this->User->id : $this->Isotope->Cart->session),
            'transactionId'		=> $objOrder->id,
            'amount'			=> (round($this->Isotope->Cart->grandTotal, 2)*100),
            'currency'			=> $this->Isotope->Config->currency,
            'paymentMethod'		=> $this->expercash_paymentMethod,
            'returnUrl'			=> $this->Environment->base . $this->addToUrl('step=complete', true) . '?uid=' . $objOrder->uniqid,
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
        if ($objOrder === null)
        {
            return false;
        }

        $strKey = md5(\Input::get('amount') . \Input::get('currency') . \Input::get('paymentMethod') . \Input::get('transactionId') . \Input::get('GuTID') . $this->expercash_popupKey);

        if (\Input::get('exportKey') != $strKey)
        {
            \System::log('ExperCash: exportKey was incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('amount') != (round($this->Isotope->Cart->grandTotal, 2)*100))
        {
            \System::log('ExperCash: amount is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('currency') != $this->Isotope->Config->currency)
        {
            \System::log('ExperCash: currency is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('transactionId') != $objOrder->id)
        {
            \System::log('ExperCash: transactionId is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        return true;
    }
}
