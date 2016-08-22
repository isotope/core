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

use GuzzleHttp\Psr7\Response;
use Haste\Http\Response\RedirectResponse;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Module\Checkout;

class PaypalPlus extends PaypalApi
{
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            Checkout::redirectToStep(Checkout::STEP_COMPLETE, $objOrder);
        }

        $request = $this->createPayment($objOrder);

        if ($request instanceof Response) {
            $responseCode = (int) $request->getStatusCode();
            $responseData = $request->getBody()->getContents();
        } else {
            $responseCode = (int) $request->code;
            $responseData = $request->response;
        }

        if (201 === $responseCode) {
            $paypalData = json_decode($responseData, true);
            $this->storePayment($objOrder, $paypalData);

            foreach ($paypalData['links'] as $link) {
                if ('approval_url' === $link['rel']) {
                    $response = new RedirectResponse($link['href'], 303);
                    $response->send();
                }
            }
        }

        \System::log('PayPayl payment failed. See paypal.log for more information.', __METHOD__, TL_ERROR);

        if ($request instanceof Response) {
            log_message(
                sprintf(
                    "PayPal API Error! (HTTP %s %s)\n\nResponse:\n%s",
                    $request->getStatusCode(),
                    $request->getReasonPhrase(),
                    $request->getBody()->getContents()
                ),
                'paypal.log'
            );
        } else {
            log_message(
                sprintf(
                    "PayPal API Error! (HTTP %s %s)\n\nRequest:\n%s\n\nResponse:\n%s",
                    $request->code,
                    $request->error,
                    $request->request,
                    $request->response
                ),
                'paypal.log'
            );
        }

        $response = new RedirectResponse(Checkout::redirectToStep(Checkout::STEP_FAILED), 303);
        $response->send();
    }

    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $paypalData = $this->retrievePayment($objOrder);

        if (0 === count($paypalData)
            || \Input::get('paymentId') !== $paypalData['id']
            || 'created' !== $paypalData['state']
        ) {
            return false;
        }

        $request = $this->patchPayment($objOrder, $paypalData['id']);

        if (200 !== $request->code) {
            return false;
        }

        $request = $this->executePayment($paypalData['id'], \Input::get('PayerID'));

        if ($request instanceof Response) {
            $responseCode = (int) $request->getStatusCode();
        } else {
            $responseCode = (int) $request->code;
        }

        if (200 !== $responseCode) {
            return false;
        }

        return true;
    }
}
