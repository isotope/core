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

use Contao\Controller;
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
use Symfony\Component\HttpFoundation\Response;

/**
 * Sparkasse payment method.
 *
 * @property string $sparkasse_paymentmethod
 * @property string $sparkasse_sslmerchant
 * @property string $sparkasse_sslpassword
 * @property string $sparkasse_merchantref
 */
class Sparkasse extends Postsale
{
    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $arrData = array();

        foreach (array('aid', 'amount', 'basketid', 'currency', 'directPosErrorCode', 'directPosErrorMessage', 'orderid', 'rc', 'retrefnum', 'sessionid', 'trefnum') as $strKey) {
            $arrData[$strKey] = Input::post($strKey);
        }

        // Sparkasse system sent error message
        if ($arrData['directPosErrorCode'] > 0) {
            return $this->redirectError($arrData);
        }

        // Check the data hash to prevent manipulations
        if (Input::post('mac') != $this->calculateHash($arrData)) {
            System::log('Security hash mismatch in Sparkasse payment!', __METHOD__, TL_ERROR);
            return $this->redirectError($arrData);
        }

        // Convert amount, Sparkasse is using comma instead of dot as decimal separator
        $arrData['amount'] = str_replace(',', '.', preg_replace('/[^0-9,]/', '', $arrData['amount']));

        // Validate payment data
        if ($objOrder->getCurrency() !== $arrData['currency']) {
            System::log(sprintf('Data manipulation: currency mismatch ("%s" != "%s")', $objOrder->getCurrency(), $arrData['currency']), __METHOD__, TL_ERROR);
            return $this->redirectError($arrData);
        }

        if ($objOrder->getTotal() != $arrData['amount']) {
            System::log(sprintf('Data manipulation: amount mismatch ("%s" != "%s")', $objOrder->getTotal(), $arrData['amount']), __METHOD__, TL_ERROR);
            return $this->redirectError($arrData);
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return new Response();
        }

        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);
            return $this->redirectError($arrData);
        }

        // Store request data in order for future references
        $arrPayment               = StringUtil::deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data   = $arrPayment;

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        $strUrl = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, PageModel::findWithDetails((int) $arrData['sessionid']), true);

        return new Response('redirecturls=' . $strUrl);
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) Input::post('orderid'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        global $objPage;

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_sparkasse');

        $objTemplate->amount = number_format($objOrder->getTotal(), 2, ',', '');
        $objTemplate->basketid = $objOrder->source_collection_id;
        $objTemplate->currency = $objOrder->getCurrency();
        $objTemplate->locale = $objOrder->language;
        $objTemplate->orderid = $objOrder->getId();
        $objTemplate->sessionid = $objPage->id;
        $objTemplate->transactiontype = ('auth' === $this->trans_type ? 'preauthorization' : 'authorization');
        $objTemplate->merchantref = '';

        if ($this->sparkasse_merchantref != '') {
            $objTemplate->merchantref = substr(Controller::replaceInsertTags($this->sparkasse_merchantref), 0, 30);
        }

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message  = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->link     = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2];

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
     *
     * @param  array $arrData
     *
     * @return string
     */
    private function calculateHash($arrData)
    {
        ksort($arrData);

        return hash_hmac('sha1', implode('', $arrData), $this->sparkasse_sslpassword);
    }

    /**
     * Redirect the Sparkasse server to our error page
     *
     * @param array $arrData
     */
    private function redirectError($arrData)
    {
        $strUrl = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, PageModel::findWithDetails((int) $arrData['sessionid']), true);

        return new Response('redirecturlf=' . $strUrl . '?reason=' . $arrData['directPosErrorMessage']);
    }
}
