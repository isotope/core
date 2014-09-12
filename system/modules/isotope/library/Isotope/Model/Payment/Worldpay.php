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

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Translation;


/**
 * Isotope payment method for www.worldpay.com
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Worldpay extends Postsale implements IsotopePayment
{

    /**
     * Process Instant Payment Notifications (IPN)
     * @param   IsotopeProductCollection
     */
    public function processPostSale(IsotopeProductCollection $objOrder)
    {
        if (\Input::post('instId') != $this->worldpay_instId) {
            \System::log('Installation ID does not match', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        // Validate payment data
        if (
            $objOrder->currency != \Input::post('currency') ||
            $objOrder->getTotal() != \Input::post('amount') ||
            $this->worldpay_callbackPW != \Input::post('callbackPW') ||
            (!$this->debug && \Input::post('testMode') == '100')
        ) {
            \System::log('Data manipulation in payment from "' . \Input::post('email') . '" !', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        // Order status cancelled and order not yet completed, do nothing
        if (\Input::post('transStatus') != 'Y' && $objOrder->status == 0) {
            $this->postsaleError();
        }

        if (\Input::post('transStatus') == 'Y') {
            if (!$objOrder->checkout()) {
                \System::log('Checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                $this->postsaleError();
            }

            $objOrder->date_paid = time();
            $objOrder->updateOrderStatus($this->new_order_status);
        }

        // Store request data in order for future references
        $arrPayment               = deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data   = $arrPayment;

        $objOrder->save();

        $this->postsaleSuccess($objOrder);
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('cartId'));
    }

    /**
     * Return the checkout form.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        global $objPage;
        $objAddress = $objOrder->getBillingAddress();

        $arrData['instId']      = $this->worldpay_instId;
        $arrData['cartId']      = $objOrder->id;
        $arrData['amount']      = number_format($objOrder->getTotal(), 2);
        $arrData['currency']    = $objOrder->currency;
        $arrData['description'] = Translation::get($this->worldpay_description);
        $arrData['name']        = substr($objAddress->firstname . ' ' . $objAddress->lastname, 0, 40);

        if ($objAddress->company != '') {
            $arrData['address1'] = substr($objAddress->company, 0, 84);
            $arrData['address2'] = substr($objAddress->street_1, 0, 84);
            $arrData['address3'] = substr($objAddress->street_2, 0, 84);
        } else {
            $arrData['address1'] = substr($objAddress->street_1, 0, 84);
            $arrData['address2'] = substr($objAddress->street_2, 0, 84);
            $arrData['address3'] = substr($objAddress->street_3, 0, 84);
        }

        $arrData['town']     = substr($objAddress->city, 0, 30);
        $arrData['region']   = substr($objAddress->subdivision, 0, 30);
        $arrData['postcode'] = substr($objAddress->postal, 0, 12);
        $arrData['country']  = strtoupper($objAddress->country);
        $arrData['tel']      = substr($objAddress->phone, 0, 30);
        $arrData['email']    = substr($objAddress->email, 0, 80);

        // Generate MD5 secret hash
        $arrData['signature'] = md5($this->worldpay_md5secret . ':' . implode(':', array_intersect_key($arrData, array_flip(trimsplit(':', $this->worldpay_signatureFields)))));

        $objTemplate = new \Isotope\Template('iso_payment_worldpay');

        $objTemplate->setData($arrData);
        $objTemplate->id       = $this->id;
        $objTemplate->pageId   = $objPage->id;
        $objTemplate->debug    = $this->debug;
        $objTemplate->action   = ($this->debug ? 'https://secure-test.worldpay.com/wcc/purchase' : 'https://secure.worldpay.com/wcc/purchase');
        $objTemplate->headline = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * Redirect client on WorldPay site to the error page
     */
    protected function postsaleError()
    {
        $objPage = \PageModel::findWithDetails((int) \Input::post('M_pageId'));
        $strUrl  = \Environment::get('base') . Checkout::generateUrlForStep('failed', null, $objPage);

        // Output a HTML page to redirect the client from WorldPay back to the shop
        echo '
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>The Tulle Factory</title>
<meta http-equiv="refresh" content="0; url=' . $strUrl . '">
</head>
<body>
Redirecting back to shop...
</body>
</html>
';
        exit;
    }

    /**
     * Redirect client on WorldPay site to the confirmation page
     * @param   IsotopeProductCollection
     */
    protected function postsaleSuccess($objOrder)
    {
        $objPage = \PageModel::findWithDetails((int) \Input::post('M_pageId'));
        $strUrl  = \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder, $objPage);

        // Output a HTML page to redirect the client from WorldPay back to the shop
        echo '
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>The Tulle Factory</title>
<meta http-equiv="refresh" content="0; url=' . $strUrl . '">
</head>
<body>
Redirecting back to shop...
</body>
</html>
';
        exit;
    }
}
