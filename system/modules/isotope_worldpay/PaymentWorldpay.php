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


/**
 * Isotope payment method for www.worldpay.com
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class PaymentWorldpay extends IsotopePayment
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
     * Process PayPal Instant Payment Notifications (IPN)
     *
     * @access public
     * @return void
     */
    public function processPostSale()
    {
        if ($this->Input->post('instId') != $this->worldpay_instId) {
            $this->log('Installation ID does not match', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('cart_id', $this->Input->post('cartId'))) {
            $this->log('Order ID "' . $this->Input->post('cartId') . '" not found', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        // Validate payment data (see #2221)
        if (
            $objOrder->currency != $this->Input->post('currency') ||
            $objOrder->grandTotal != $this->Input->post('amount') ||
            $this->worldpay_callbackPW != $this->Input->post('callbackPW') ||
            (!$this->debug && $this->Input->post('testMode') == '100')
        ) {
            $this->log('Data manipulation in payment from "' . $this->Input->post('email') . '" !', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        // Order status cancelled and order not yet completed, do nothing
        if ($this->Input->post('transStatus') != 'Y' && $objOrder->status == 0) {
            $this->postsaleError();
        }

        if ($this->Input->post('transStatus') == 'Y') {
            if (!$objOrder->checkout()) {
                $this->log('Checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                $this->postsaleError();
            }

            $objOrder->date_paid = time();
        }

        // Store request data in order for future references
        $arrPayment = deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data = $arrPayment;

        $objOrder->save();

        $this->postsaleSuccess();
    }


    /**
     * Return the PayPal form.
     *
     * @access public
     * @return string
     */
    public function checkoutForm()
    {
        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id)) {
            $this->redirect($this->addToUrl('step=failed', true));
        }

        global $objPage;
        $objAddress = $this->Isotope->Cart->billingAddress;

        $arrData['instId'] = $this->worldpay_instId;
        $arrData['cartId'] = $this->Isotope->Cart->id;
        $arrData['amount'] = number_format($this->Isotope->Cart->grandTotal, 2);
        $arrData['currency'] = $this->Isotope->Config->currency;
        $arrData['description'] = $this->worldpay_description;
        $arrData['name'] = substr($objAddress->firstname . ' ' . $objAddress->lastname, 0, 40);

        if ($objAddress->company != '') {
            $arrData['address1'] = substr($objAddress->company, 0, 84);
            $arrData['address2'] = substr($objAddress->street_1, 0, 84);
            $arrData['address3'] = substr($objAddress->street_2, 0, 84);
        } else {
            $arrData['address1'] = substr($objAddress->street_1, 0, 84);
            $arrData['address2'] = substr($objAddress->street_2, 0, 84);
            $arrData['address3'] = substr($objAddress->street_3, 0, 84);
        }

        $arrData['town'] = substr($objAddress->city, 0, 30);
        $arrData['region'] = substr($objAddress->subdivision, 0, 30);
        $arrData['postcode'] = substr($objAddress->postal, 0, 12);
        $arrData['country'] = strtoupper($objAddress->country);
        $arrData['tel'] = substr($objAddress->phone, 0, 30);
        $arrData['email'] = substr($objAddress->email, 0, 80);

        // Generate MD5 secret hash
        $arrData['signature'] = md5($this->worldpay_md5secret . ':' . implode(':', array_intersect_key($arrData, array_flip(trimsplit(':', $this->worldpay_signatureFields)))));

        $objTemplate = new IsotopeTemplate('iso_payment_worldpay');

        $objTemplate->setData($arrData);
        $objTemplate->id = $this->id;
        $objTemplate->pageId = $objPage->id;
        $objTemplate->debug = $this->debug;
        $objTemplate->action = ($this->debug ? 'https://secure-test.worldpay.com/wcc/purchase' : '');

        return $objTemplate->parse();
    }


    protected function postsaleError($objOrder)
    {
        $objPage = $this->getPageDetails((int) $this->Input->post('M_pageId'));
        $strUrl = $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/failed', $objPage->language);

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


    protected function postsaleSuccess()
    {
        $strUrl = $this->Environment->base . $this->generateFrontendUrl($objPage->row(), '/step/confirm', $objPage->language) . '?uid=' . $objOrder->uniqid;
        $objPage = $this->getPageDetails((int) $this->Input->post('M_pageId'));

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
