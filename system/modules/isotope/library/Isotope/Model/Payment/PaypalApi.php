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

use Contao\Request;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
use Isotope\Module\Checkout;

/**
 * @property string $paypal_plus_client
 * @property string $paypal_plus_secret
 */
abstract class PaypalApi extends Payment
{

    public function createPayment(IsotopePurchasableCollection $order)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = [
                'name'  => $item->name,
                'sku'   => $item->sku,
                'price' => $item->getPrice(),
                'currency' => $order->getCurrency(),
                'quantity' => $item->quantity,
            ];
        }

        $data = [
            'intent'        => 'sale',
            'redirect_urls' => [
                'return_url' => \Environment::get('base') . Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $order),
                'cancel_url' => \Environment::get('base') . Checkout::generateUrlForStep(Checkout::STEP_FAILED),
            ],
            'payer'         => [
                'payment_method' => 'paypal',
            ],
            'transactions'  => [
                [
                    'amount'      => [
                        'total'    => $order->getTotal(),
                        'currency' => $order->getCurrency(),
                    ],
                    'description' => 'This is the payment transaction description.',
                    'item_list' => [
                        'items' => $items
                    ]
                ],
            ],
        ];

        return $this->sendRequest('/payments/payment', json_encode($data), 'POST');
    }

    public function patchPayment(IsotopePurchasableCollection $order, $paymentId)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $data = [
            [
                'op'    => 'replace',
                'path'  => '/payer/payer_info',
                'value' => [
                    'email'            => $billingAddress->email,
                    'first_name'       => $billingAddress->firstname,
                    'last_name'        => $billingAddress->lastname,
                    'shipping_address' => [
                        'recipient_name' => $shippingAddress->firstname . ' ' . $shippingAddress->lastname,
                        'line1'          => $shippingAddress->street_1,
                        'line2'          => $shippingAddress->street_2,
                        'city'           => $shippingAddress->city,
                        'state'          => $shippingAddress->subdivision,
                        'phone'          => $shippingAddress->phone,
                        'postal_code'    => $shippingAddress->postal,
                        'country_code'   => $shippingAddress->country,
                    ],
                ],
            ],
        ];

        return $this->sendRequest('/payments/payment/' . $paymentId, json_encode($data), 'PATCH');
    }

    public function executePayment($paymentId, $payerId)
    {
        $data = [
            'payer_id' => $payerId,
        ];

        return $this->sendRequest('/payments/payment/' . $paymentId . '/execute', json_encode($data), 'POST');
    }

    protected function storePayment(IsotopeProductCollection $collection, array $paypalData)
    {
        $paymentData = deserialize($collection->payment_data, true);
        $paymentData['PAYPAL_PLUS'] = $paypalData;

        $collection->payment_data = $paymentData;
        $collection->save();
    }

    protected function retrievePayment(IsotopeProductCollection $collection)
    {
        $paymentData = deserialize($collection->payment_data, true);

        return array_key_exists('PAYPAL_PLUS', $paymentData) ? $paymentData['PAYPAL_PLUS'] : [];
    }

    /**
     * @return array|null
     */
    private function getApiToken()
    {
        $request = $this->prepareRequest();
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->username = $this->paypal_plus_client;
        $request->password = $this->paypal_plus_secret;

        $request->send(
            $this->getApiUrl('/oauth2/token'),
            'grant_type=client_credentials',
            'POST'
        );

        if ($request->code != 200) {
            return null;
        }

        $response = json_decode($request->response, true);

        return array_key_exists('access_token', $response) ? $response : null;
    }

    private function sendRequest($path, $data = null, $method = null, $renewToken = false)
    {
        $request = $this->prepareRequest();

        // TODO store and reuse token
        $token = $this->getApiToken();
        $request->setHeader('Authorization', $token['token_type'] . ' ' . $token['access_token']);
        $request->setHeader('Content-Type', 'application/json');

        $request->send($this->getApiUrl($path), $data, $method);

        // Token probably expired, try again with a new token
        if (401 === $request->code && !$renewToken) {
            return $this->sendRequest($path, $data, $method, true);
        }

        return $request;
    }


    private function prepareRequest()
    {
        $request = new Request();
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('Accept-Language', 'en_US');

        return $request;
    }

    private function getApiUrl($path)
    {
        return 'https://api.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/v1' . $path;
    }
}
