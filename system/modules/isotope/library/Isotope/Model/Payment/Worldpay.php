<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Contao\Environment;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Isotope\Translation;

/**
 * Isotope payment method for www.worldpay.com
 *
 * @property int    $worldpay_instId
 * @property string $worldpay_callbackPW
 * @property string $worldpay_signatureFields
 * @property string $worldpay_md5secret
 * @property string $worldpay_description
 */
class Worldpay extends Postsale
{
    /**
     * @inheritdoc
     */
    public function processPostSale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        if (Input::post('instId') != $this->worldpay_instId) {
            System::log('Installation ID does not match', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        // Validate payment data
        if (
            $objOrder->getCurrency() != Input::post('currency') ||
            $objOrder->getTotal() != Input::post('amount') ||
            $this->worldpay_callbackPW != Input::post('callbackPW') ||
            (!$this->debug && Input::post('testMode') == '100')
        ) {
            System::log('Data manipulation in payment from "' . Input::post('email') . '" !', __METHOD__, TL_ERROR);
            $this->postsaleError();
        }

        // Order status cancelled and order not yet completed, do nothing
        if ('Y' !== Input::post('transStatus') && $objOrder->status == 0) {
            $this->postsaleError();
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            $this->postsaleSuccess($objOrder);
        }

        if ('Y' === Input::post('transStatus')) {
            if (!$objOrder->checkout()) {
                System::log('Checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);
                $this->postsaleError();
            }

            $objOrder->setDatePaid(time());
            $objOrder->updateOrderStatus($this->new_order_status);
        }

        // Store request data in order for future references
        $arrPayment               = StringUtil::deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data   = $arrPayment;

        $objOrder->save();

        $this->postsaleSuccess($objOrder);
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) Input::post('cartId'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        global $objPage;
        $objAddress = $objOrder->getBillingAddress();

        $arrData                = array();
        $arrData['instId']      = $this->worldpay_instId;
        $arrData['cartId']      = $objOrder->getId();
        $arrData['amount']      = number_format($objOrder->getTotal(), 2);
        $arrData['currency']    = $objOrder->getCurrency();
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
        $arrData['signature'] = md5($this->worldpay_md5secret . ':' . implode(':', array_intersect_key($arrData, array_flip(StringUtil::trimsplit(':', $this->worldpay_signatureFields)))));

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_worldpay');

        $objTemplate->setData($arrData);
        $objTemplate->id       = $this->id;
        $objTemplate->pageId   = $objPage->id;
        $objTemplate->debug    = $this->debug;
        $objTemplate->action   = ($this->debug ? 'https://secure-test.worldpay.com/wcc/purchase' : 'https://secure.worldpay.com/wcc/purchase');
        $objTemplate->headline = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * Redirect client on WorldPay site to the error page
     */
    protected function postsaleError()
    {
        $objPage = PageModel::findWithDetails((int) Input::post('M_pageId'));
        $strUrl  = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, $objPage, true);

        // Output a HTML page to redirect the client from WorldPay back to the shop
        echo '
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Isotope eCommerce</title>
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
     *
     * @param IsotopeProductCollection $objOrder
     */
    protected function postsaleSuccess(IsotopeProductCollection $objOrder)
    {
        $objPage = PageModel::findWithDetails((int) Input::post('M_pageId'));
        $strUrl  = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, $objPage, true);

        // Output a HTML page to redirect the client from WorldPay back to the shop
        echo '
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Isotope eCommerce</title>
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
