<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Haste\Http\Response\Response;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;

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
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        $arrData = array();

        foreach (array('aid', 'amount', 'basketid', 'currency', 'directPosErrorCode', 'directPosErrorMessage', 'orderid', 'rc', 'retrefnum', 'sessionid', 'trefnum') as $strKey) {
            $arrData[$strKey] = \Input::post($strKey);
        }

        // Sparkasse system sent error message
        if ($arrData['directPosErrorCode'] > 0) {
            $this->redirectError($arrData);
        }

        // Check the data hash to prevent manipulations
        if (\Input::post('mac') != $this->calculateHash($arrData)) {
            \System::log('Security hash mismatch in Sparkasse payment!', __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        // Convert amount, Sparkasse is using comma instead of dot as decimal separator
        $arrData['amount'] = str_replace(',', '.', preg_replace('/[^0-9,]/', '', $arrData['amount']));

        // Validate payment data
        if ($objOrder->currency != $arrData['currency']) {
            \System::log(sprintf('Data manipulation: currency mismatch ("%s" != "%s")', $objOrder->currency, $arrData['currency']), __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        } elseif ($objOrder->getTotal() != $arrData['amount']) {
            \System::log(sprintf('Data manipulation: amount mismatch ("%s" != "%s")', $objOrder->getTotal(), $arrData['amount']), __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
            $this->redirectError($arrData);
        }

        // Store request data in order for future references
        $arrPayment               = deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data   = $arrPayment;

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        $strUrl = Checkout::generateUrlForStep('complete', $objOrder, \PageModel::findWithDetails((int) $arrData['sessionid']));

        // 200 OK
        $objResponse = new Response('redirecturls=' . \Environment::get('base') . $strUrl);
        $objResponse->send();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('orderid'));
    }

    /**
     * Return the payment form.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        global $objPage;

        $objTemplate = new Template('iso_payment_sparkasse');

        $objTemplate->amount = number_format($objOrder->getTotal(), 2, ',', '');
        $objTemplate->basketid = $objOrder->source_collection_id;
        $objTemplate->currency = $objOrder->currency;
        $objTemplate->locale = $objOrder->language;
        $objTemplate->orderid = $objOrder->id;
        $objTemplate->sessionid = $objPage->id;
        $objTemplate->transactiontype = ($this->trans_type == 'auth' ? 'preauthorization' : 'authorization');
        $objTemplate->merchantref = '';

        if ($this->sparkasse_merchantref != '') {
            $objTemplate->merchantref = substr($this->replaceInsertTags($this->sparkasse_merchantref), 0, 30);
        }

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->link = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2];

        // Unfortunately we can't use the class method for this
        // @todo change when PHP 5.4 is compulsory
        $objTemplate->calculateHash = function($arrData) {
            ksort($arrData);

            return hash_hmac('sha1', implode('', $arrData), $this->sparkasse_sslpassword);
        };

        return $objTemplate->parse();
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
     * @param array
     */
    private function redirectError($arrData)
    {
        $strUrl = Checkout::generateUrlForStep('failed', null, \PageModel::findWithDetails((int) $arrData['sessionid']));

        // 200 OK
        $objResponse = new Response('redirecturlf=' . \Environment::get('base') . $strUrl . '?reason=' . $arrData['directPosErrorMessage']);
        $objResponse->send();
    }
}
