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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;


/**
 * Class Expercash
 *
 * @copyright Isotope eCommerce Workgroup 2009-2012
 * @author    Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Expercash extends Payment implements IsotopePayment, IsotopePostsale
{

    /**
     * processPayment function.
     *
     * @access  public
     * @return  mixed|
     */
    public function processPayment()
    {
        $objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id);

        if ($this->validateUrlParams($objOrder))
        {
            return true;
        }

        \Isotope\Module\Checkout::redirectToStep('failed');
    }


    /**
     * Process PayPal Instant Payment Notifications (IPN)
     *
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$this->validateUrlParams($objOrder)) {
            \System::log('ExperCash: data rejected' . print_r($_POST, true), __METHOD__, TL_GENERAL);
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('invoice') . '" failed', __METHOD__, TL_ERROR);
            return;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        header('HTTP/1.1 200 OK');
        exit;
    }

    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::get('transactionId'));
    }


    /**
     * Return the PayPal form.
     *
     * @access public
     * @return string
     */
    public function checkoutForm()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null)
        {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $arrData = array
        (
            'popupId'       => $this->expercash_popupId,
            'jobId'         => microtime(),
            'functionId'    => (FE_USER_LOGGED_IN ? \FrontendUser::getInstance()->id : Isotope::getCart()->session),
            'transactionId' => $objOrder->id,
            'amount'        => (round(Isotope::getCart()->getTotal(), 2)*100),
            'currency'      => Isotope::getConfig()->currency,
            'paymentMethod' => $this->expercash_paymentMethod,
            'returnUrl'     => \Environment::get('base') . \Haste\Util\Url::addQueryString('uid=' . $objOrder->uniqid, \Isotope\Module\Checkout::generateUrlForStep('complete')),
            'errorUrl'      => \Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed'),
            'notifyUrl'     => \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id,
            'profile'       => $this->expercash_profile,
        );

        $strKey = '';
        $strUrl = 'https://epi.expercash.net/epi_popup2.php?';

        foreach ($arrData as $k => $v)
        {
            $strKey .= $v;
            $strUrl .= $k . '=' . urlencode($v) . '&amp;';
        }

        if (is_file(TL_ROOT . '/' . $this->expercash_css))
        {
            $strUrl .= 'cssUrl=' . urlencode(\Environment::get('base') . $this->expercash_css) . '&amp;';
        }

        $strUrl .= 'language=' . strtoupper($GLOBALS['TL_LANGUAGE']) . '&amp;popupKey=' . md5($strKey.$this->expercash_popupKey);

        $strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>

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

        if (\Input::get('amount') != (round(Isotope::getCart()->getTotal(), 2)*100))
        {
            \System::log('ExperCash: amount is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('currency') != Isotope::getConfig()->currency)
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
