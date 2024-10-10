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
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Module\Checkout;
use Isotope\Template;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PaypalCheckout extends PaypalApi
{
    /**
     * List of parameters to enable funding
     * @var array
     */
    public static $enableFundingParameters = array(
        'card',
        'credit',
        'paylater',
        'venmo',
        'bancontact',
        'blik',
        'eps',
        'giropay',
        'ideal',
        'mercadopago',
        'mybank',
        'p24',
        'sepa',
        'sofort'
    );

    public function isAvailable(): bool
    {
        if (!in_array(Isotope::getConfig()->currency, ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'])) {
            return false;
        }

        return parent::isAvailable();
    }

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
                    $template = new Template('iso_payment_paypal_checkout');
                    $template->setData($this->arrData);

                    $template->client_id = $this->paypal_client;
                    $template->currency = $objOrder->getCurrency();
                    $template->enable_funding = $this->getEnableFundingParameters();

                    parse_str(parse_url($link['href'], PHP_URL_QUERY), $params);
                    $template->token = $params['token'];

                    $successUrl = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true);
                    $successUrl = Url::addQueryString('paymentID=__paymentID__', $successUrl);
                    $successUrl = Url::addQueryString('payerID=__payerID__', $successUrl);
                    $template->success_url = $successUrl;

                    $template->cancel_url = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true);

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
            || Input::get('paymentID') !== $paypalData['id']
            || 'created' !== $paypalData['state']
        ) {
            return false;
        }

        try {
            $response = $this->executePayment($paypalData['id'], Input::get('payerID'));
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

    /**
     * Get list of model types
     *
     * @return array
     */
    public static function getFundingParameters()
    {
        return static::$enableFundingParameters;
    }

    /**
     * Return options list of model types
     *
     * @return array
     */
    public static function getEnableFundingOptions()
    {
        $arrOptions = array();

        foreach (static::getFundingParameters() as $strName => $strClass) {
            $arrOptions[$strClass] = $GLOBALS['TL_LANG']['tl_iso_payment']['paypal_enable_funding_options'][$strClass] ?? $strClass;
        }

        return $arrOptions;
    }

    /**
     * Get enable funding parameters
     *
     * @return null|string
     */
    public function getEnableFundingParameters()
    {
        if (isset($this->paypal_enable_funding)) {
            return implode(',', array_values(unserialize($this->paypal_enable_funding)));
        }

        return null;
    }
}
