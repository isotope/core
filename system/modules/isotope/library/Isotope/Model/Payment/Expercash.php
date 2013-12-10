<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Haste\Http\Response\Response;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
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
     * Process payment on checkout page.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        // @todo this can't be the only validation
        if ($this->validateUrlParams($objOrder)) {
            return true;
        }

        return false;
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
            \System::log('Postsale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        // 200 OK
        $objResponse = new Response();
        $objResponse->send();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::get('transactionId'));
    }

    /**
     * Return the PayPal form.
     *
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $arrData = array
        (
            'popupId'       => $this->expercash_popupId,
            'jobId'         => microtime(),
            'functionId'    => ($objOrder->member ?: $objOrder->uniqid),
            'transactionId' => $objOrder->id,
            'amount'        => (round($objOrder->getTotal(), 2) * 100),
            'currency'      => $objOrder->currency,
            'paymentMethod' => $this->expercash_paymentMethod,
            'returnUrl'     => \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder),
            'errorUrl'      => \Environment::get('base') . $objModule->generateUrlForStep('failed'),
            'notifyUrl'     => \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id,
            'profile'       => $this->expercash_profile,
        );

        $strKey = '';
        $strUrl = 'https://epi.expercash.net/epi_popup2.php?';

        foreach ($arrData as $k => $v) {
            $strKey .= $v;
            $strUrl .= $k . '=' . urlencode($v) . '&amp;';
        }

        if (is_file(TL_ROOT . '/' . $this->expercash_css)) {
            $strUrl .= 'cssUrl=' . urlencode(\Environment::get('base') . $this->expercash_css) . '&amp;';
        }

        $strUrl .= 'language=' . strtoupper($GLOBALS['TL_LANGUAGE']) . '&amp;popupKey=' . md5($strKey . $this->expercash_popupKey);

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
        if ($objOrder === null) {
            return false;
        }

        $strKey = md5(\Input::get('amount') . \Input::get('currency') . \Input::get('paymentMethod') . \Input::get('transactionId') . \Input::get('GuTID') . $this->expercash_popupKey);

        if (\Input::get('exportKey') != $strKey) {
            \System::log('ExperCash: exportKey was incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('amount') != (round($objOrder->getTotal(), 2) * 100)) {
            \System::log('ExperCash: amount is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('currency') != $objOrder->currency) {
            \System::log('ExperCash: currency is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (\Input::get('transactionId') != $objOrder->id) {
            \System::log('ExperCash: transactionId is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        return true;
    }
}
