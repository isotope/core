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

use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Paybyway extends Payment implements IsotopePostsale
{
    /**
     * Paybyway only supports EUR currency
     *
     * @inheritdoc
     */
    public function isAvailable()
    {
        $objConfig = Isotope::getConfig();

        if (null === $objConfig || 'EUR' !== $objConfig->currency) {
            return false;
        }

        return parent::isAvailable();
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_paybyway');

        $objTemplate->action   = 'https://www.paybyway.com/e-payments/pay';
        $objTemplate->headline = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        $objTemplate->merchant_id = (int) $this->paybyway_merchant_id;
        $objTemplate->amount = round($objOrder->getTotal() * 100);
        $objTemplate->currency = 'EUR';
        $objTemplate->order_number = $objOrder->getId();
        $objTemplate->lang = ('fi' === $GLOBALS['TL_LANGUAGE'] ? 'FI' : 'EN');

        $postsaleUrl = System::getContainer()->get('router')->generate('isotope_postsale', ['mod' => 'pay', 'id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);
        $objTemplate->return_address = $postsaleUrl;
        $objTemplate->cancel_address = $postsaleUrl;

        if ($this->debug) {
            $objTemplate->action = 'https://www.paybyway.com/e-payments/test_pay';
            $this->paybyway_private_key = 'private_key';
        }

        $objTemplate->authcode = strtoupper(md5(
            $this->paybyway_private_key .
            '|' . $objTemplate->merchant_id .
            '|' . $objTemplate->amount .
            '|' . $objTemplate->currency .
            '|' . $objTemplate->order_number .
            '|' . $objTemplate->lang .
            '|' . $objTemplate->return_address .
            '|' . $objTemplate->cancel_address
        ));

        return $objTemplate->parse();
    }

    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if ($objOrder->isLocked()) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        if ($this->debug) {
            $this->paybyway_private_key = 'private_key';
        }

        $strChecksum = strtoupper(md5(
            $this->paybyway_private_key .
            '|' . Input::post('RETURN_CODE') .
            '|' . Input::post('ORDER_NUMBER') .
            (Input::post('SETTLED') ? ('|' . Input::post('SETTLED')) : '') .
            (Input::post('INCIDENT_ID') ? ('|' . Input::post('INCIDENT_ID')) : '')
        ));

        if (Input::post('AUTHCODE') != $strChecksum) {
            System::log('Postsale manipulation for order ID ' . $objOrder->getId(), __METHOD__, TL_ERROR);
            Checkout::redirectToStep('failed');
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Paybyway checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return;
        }

        switch (Input::post('RETURN_CODE')) {

            case 0: // Payment completed successfully.
                if ($objOrder->checkout()) {
                    $objOrder->setDatePaid(time());
                    $objOrder->updateOrderStatus($this->new_order_status);
                    Checkout::redirectToStep('complete', $objOrder);
                }
                break;

            case 4: // Transaction status could not be updated after customer returned from the web page of a bank. Please use the merchant UI to resolve the payment status.
                if (null === $objOrder->getConfig()) {
                    System::log('Config for Order ID ' . $objOrder->getId() . ' not found', __METHOD__, TL_ERROR);

                } elseif ($objOrder->checkout()) {
                    $objOrder->updateOrderStatus($objOrder->getConfig()->orderstatus_error);
                    Checkout::redirectToStep('complete', $objOrder);
                }
                break;

            case 1: // Payment failed. Customer did not successfully finish the payment.
            case 2: // Duplicate order number. You have reused an order number. Make sure that your order numbers are unique, and are not reused in any case.
            case 3: // User disabled. Either your Paybyway account has been temporarily disabled for security reasons, or your sub-merchant is disabled.
            case 10: // Maintenance break. The transaction is not created and the user has been notified and transferred back to the cancel address.
                // Do nothing here, we redirect to "failed" by default
                break;
        }

        System::log('Paybyway checkout failed for order ID ' . $objOrder->getId(), __METHOD__, TL_ERROR);

        Checkout::redirectToStep('failed');
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(Input::post('ORDER_NUMBER'));
    }
}
