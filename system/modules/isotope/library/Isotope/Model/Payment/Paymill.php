<?php

/**
 * Copyright (C) 2017 Comolo GmbH
 *
 * @author    Hendrik Obermayer
 * @copyright 2017 Comolo GmbH <https://www.comolo.de>
 * @license   MIT
 */

namespace Isotope\Model\Payment;

use System;
use Environment;
use RequestToken;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment\Postsale;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Isotope\Currency;
use Paymill\Models\Request\Transaction as PaymillTransaction;
use Paymill\Request as PaymillRequest;
use Paymill\Services\PaymillException;

/**
 * Paymill payment method
 *
 * @property string $paymill_private_key
 * @property string $paymill_public_key
 */
class Paymill extends Postsale
{
    /**
     * Return information in the backend.
     *
     * @param integer
     * @return string
     */
    public function backendInterface($orderId)
    {
        $database = \Database::getInstance();
        $order = $database
            ->prepare("SELECT * FROM tl_iso_product_collection WHERE id LIKE ?")
            ->limit(1)
            ->execute($orderId);
        $template = new \BackendTemplate('be_iso_payment_paymill');
        $template->order = $order;
        $template->payment_data = unserialize($order->payment_data);

        return $template->parse();
    }

    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if ($objOrder->order_status < 1) {
            $this->processPostsale($objOrder);
        }

        return parent::processPayment($objOrder, $objModule);
    }

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $paymillToken = \Input::post('paymillToken');

        if (!$paymillToken || empty($paymillToken)) {
            return false;
        }

        $transaction = new PaymillTransaction();
        $transaction
            ->setAmount(Currency::getAmountInMinorUnits($objOrder->getTotal(), $objOrder->getCurrency()))
            ->setCurrency($objOrder->getCurrency())
            ->setToken($paymillToken)
            ->setDescription('#'.$objOrder->getId())
        ;

        try {
            $request = new PaymillRequest($this->paymill_private_key);
            $response = $request->create($transaction);

            /**
             * @see: https://developers.paymill.com/API/index#response-codes
             */
            if ($response->getResponseCode() == 20000) {
                // Received payment
                $objOrder->payment_data = [
                    'paymill_code' => $response->getResponseCode(),
                    'paymill_status' => $response->getStatus(),
                    'paymill_id' => $response->getId(),
                ];
                $objOrder->setDatePaid(time());
                $objOrder->updateOrderStatus($this->new_order_status);
                $objOrder->save();

                return true;
            }

            return false;

        } catch(PaymillException $e){
            System::log('Paymill error. Order "' . $objOrder->getId() . '". Paymill Status: '.$e->getResponseCode() .' Error: '.$e->getErrorMessage(), __METHOD__, TL_ERROR);
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) \Input::get('id'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_paymill');

        $objTemplate->setData($this->arrData);
        $objTemplate->id = $objOrder->getId();
        $objTemplate->amount = Currency::getAmountInMinorUnits($objOrder->getTotal(), $objOrder->getCurrency());
        $objTemplate->currency = $objOrder->getCurrency();
        $objTemplate->paymill_public_key = $this->paymill_public_key;
        $objTemplate->address = $objOrder->getBillingAddress();


        $objTemplate->request_token = RequestToken::get();
        $objTemplate->action = Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder);
        $objTemplate->cancel_return = Environment::get('base') . Checkout::generateUrlForStep('failed');

        $objTemplate->headline = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->blabel = specialchars($GLOBALS['TL_LANG']['MSC']['goBack']);

        return $objTemplate->parse();
    }
}
