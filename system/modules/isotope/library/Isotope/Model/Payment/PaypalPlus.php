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
use Contao\System;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Module\Checkout;
use Isotope\Template;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PaypalPlus extends PaypalApi
{
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            Checkout::redirectToStep(Checkout::STEP_COMPLETE, $objOrder);
        }

        try {
            $response = $this->createPayment($objOrder);
        } catch (TransportExceptionInterface $e) {
            System::log('PayPayl payment failed. See paypal.log for more information.', __METHOD__, TL_ERROR);
            Checkout::redirectToStep(Checkout::STEP_FAILED);
        }

        $this->debugLog($response->getContent(false));

        if (201 === $response->getStatusCode()) {
            $paypalData = $response->toArray();
            $this->storePayment($objOrder, $paypalData);
            $this->storeHistory($objOrder, $paypalData);

            $this->patchPayment($objOrder, $paypalData['id']);

            foreach ($paypalData['links'] as $link) {
                if ('approval_url' === $link['rel']) {
                    $template = new Template('iso_payment_paypal_plus');
                    $template->setData($this->arrData);
                    $template->approval_url = $link['href'];
                    $template->mode = $this->debug ? 'sandbox' : 'live';
                    $template->country = strtoupper($objOrder->getBillingAddress()->country);

                    return $template->parse();
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $paypalData = $this->retrievePayment($objOrder);

        if (0 === \count($paypalData)
            || Input::get('paymentId') !== $paypalData['id']
            || 'created' !== $paypalData['state']
        ) {
            return false;
        }

        try {
            $response = $this->executePayment($paypalData['id'], Input::get('PayerID'));
        } catch (TransportExceptionInterface $e) {
            return false;
        }

        $this->debugLog($response->getContent(false));

        if (200 !== $response->getStatusCode()) {
            return false;
        }

        $this->storeHistory($objOrder, $response->toArray());

        $objOrder->checkout();
        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);
        $objOrder->save();

        return true;
    }
}
