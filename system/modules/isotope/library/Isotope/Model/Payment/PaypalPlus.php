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

use Haste\Http\Response\RedirectResponse;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
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
        
        if (200 === $request->code) {
            $paypalData = json_decode($request->response, true);
            $this->storePayment($objOrder, $paypalData);

            foreach ($paypalData['links'] as $link) {
                if ('approval_url' === $link['rel']) {
                    $response = new RedirectResponse($link['href'], 303);
                    $response->send();
                }
            }
        }

        // TODO add real error message
        return 'Error';
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

        if (200 !== $request->code) {
            return false;
        }

        return true;
    }
}
