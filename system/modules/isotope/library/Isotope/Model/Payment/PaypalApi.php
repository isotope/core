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

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
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
    /**
     * @param IsotopePurchasableCollection $order
     *
     * @return \Psr\Http\Message\ResponseInterface|\RequestExtended
     */
    public function createPayment(IsotopePurchasableCollection $order)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = [
                'name'  => strip_tags($item->name),
                'sku'   => $item->sku,
                'price' => number_format($item->getPrice(), 2),
                'currency' => $order->getCurrency(),
                'quantity' => $item->quantity,
            ];
        }

        foreach ($order->getSurcharges() as $surcharge) {
            if (!$surcharge->addToTotal) {
                continue;
            }

            $items[] = [
                'name'  => strip_tags($surcharge->label),
                'price' => number_format($surcharge->total_price, 2),
                'currency' => $order->getCurrency(),
                'quantity' => '1',
            ];
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $data = [
            'intent'        => 'sale',
            'redirect_urls' => [
                'return_url' => \Environment::get('base') . Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $order),
                'cancel_url' => \Environment::get('base') . Checkout::generateUrlForStep(Checkout::STEP_FAILED),
            ],
            'payer'         => [
                'payment_method' => 'paypal',
                'payer_info' => [
                    'email'            => $billingAddress->email,
                    #'salutation'       => $billingAddress->salutation,
                    'first_name'       => $billingAddress->firstname,
                    'last_name'        => $billingAddress->lastname,
                    #'phone'            => $billingAddress->phone,
                    #'birth_date'       => $billingAddress->dateOfBirth ? date('Y-m-d', $billingAddress->dateOfBirth) : '',
                    'billing_address'  => [
                        #'recipient_name' => $billingAddress->firstname . ' ' . $billingAddress->lastname,
                        'line1'          => $billingAddress->street_1,
                        #'line2'          => $billingAddress->street_2,
                        'city'           => $billingAddress->city,
                        #'state'          => $billingAddress->subdivision,
                        #'phone'          => $billingAddress->phone,
                        'postal_code'    => $billingAddress->postal,
                        'country_code'   => strtoupper($billingAddress->country),
                    ],
                    /*'shipping_address' => [
                        'recipient_name' => $shippingAddress->firstname . ' ' . $shippingAddress->lastname,
                        'line1'          => $shippingAddress->street_1,
                        #'line2'          => $shippingAddress->street_2,
                        'city'           => $shippingAddress->city,
                        #'state'          => $shippingAddress->subdivision,
                        #'phone'          => $shippingAddress->phone,
                        'postal_code'    => $shippingAddress->postal,
                        'country_code'   => strtoupper($shippingAddress->country),
                    ],*/
                ]
            ],
            'potential_payer_info' => [
                'billing_address'  => [
                    #'recipient_name' => $billingAddress->firstname . ' ' . $billingAddress->lastname,
                    'line1'          => $billingAddress->street_1,
                    'line2'          => $billingAddress->street_2,
                    'city'           => $billingAddress->city,
                    'state'          => $billingAddress->subdivision,
                    #'phone'          => $billingAddress->phone,
                    'postal_code'    => $billingAddress->postal,
                    'country_code'   => strtoupper($billingAddress->country),
                ]
            ],
            'transactions'  => [
                [
                    'amount'      => [
                        'total'    => number_format($order->getTotal(), 2),
                        'currency' => $order->getCurrency(),
                    ],
                    #'description' => 'This is the payment transaction description.',
                    'item_list' => [
                        'items' => $items
                    ]
                ],
            ],
        ];

        log_message(print_r($data, true), 'paypal.log');

        return $this->sendRequest('/payments/payment', $data, 'POST');
    }

    /**
     * @param IsotopePurchasableCollection $order
     * @param string                       $paymentId
     *
     * @return \Psr\Http\Message\ResponseInterface|\RequestExtended
     */
    public function patchPayment(IsotopePurchasableCollection $order, $paymentId)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $data = [
            [
                'op'    => 'add',
                'path'  => '/transactions/0/item_list/shipping_address',
                'value' => [
                    'recipient_name' => $shippingAddress->firstname . ' ' . $shippingAddress->lastname,
                    'line1'          => $shippingAddress->street_1,
                    #'line2'          => $shippingAddress->street_2,
                    'city'           => $shippingAddress->city,
                    'state'          => $shippingAddress->subdivision,
                    #'phone'          => $shippingAddress->phone,
                    'postal_code'    => $shippingAddress->postal,
                    'country_code'   => strtoupper($shippingAddress->country),
                ],
            ],
        ];

        return $this->sendRequest('/payments/payment/' . $paymentId, $data, 'PATCH');
    }

    /**
     * @param string $paymentId
     * @param string $payerId
     *
     * @return \Psr\Http\Message\ResponseInterface|\RequestExtended
     */
    public function executePayment($paymentId, $payerId)
    {
        $data = [
            'payer_id' => $payerId,
        ];

        return $this->sendRequest('/payments/payment/' . $paymentId . '/execute', $data, 'POST');
    }

    /**
     * @param IsotopeProductCollection $collection
     * @param array                    $paypalData
     */
    protected function storePayment(IsotopeProductCollection $collection, array $paypalData)
    {
        $paymentData = deserialize($collection->payment_data, true);
        $paymentData['PAYPAL_PLUS'] = $paypalData;

        $collection->payment_data = $paymentData;
        $collection->save();
    }

    /**
     * @param IsotopeProductCollection $collection
     *
     * @return array
     */
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

        if ($request instanceof Client) {
            $response = $request->post(
                $this->getApiUrl('/oauth2/token'),
                [
                    RequestOptions::FORM_PARAMS => ['grant_type' => 'client_credentials'],
                    RequestOptions::HEADERS     => [
                        'Authorization' => 'Basic ' . base64_encode($this->paypal_plus_client . ':' . $this->paypal_plus_secret),
                    ],
                ]
            );

            if ($response->getStatusCode() != 200) {
                return null;
            }

            $response = json_decode($response->getBody()->getContents(), true);

        } else {
            $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $request->setHeader('Authorization', 'Basic ' . base64_encode($this->paypal_plus_client . ':' . $this->paypal_plus_secret));

            $request->send(
                $this->getApiUrl('/oauth2/token'),
                'grant_type=client_credentials',
                'POST'
            );

            if ($request->code != 200) {
                return null;
            }

            $response = json_decode($request->response, true);
        }

        return array_key_exists('access_token', $response) ? $response : null;
    }

    /**
     * @param      $path
     * @param null $data
     * @param null $method
     * @param bool $renewToken
     *
     * @return \Psr\Http\Message\ResponseInterface|\RequestExtended
     */
    private function sendRequest($path, array $data = null, $method = null, $renewToken = false)
    {
        $request = $this->prepareRequest();

        // TODO store and reuse token
        $token = $this->getApiToken();

        if ($request instanceof Client) {
            $response = $request->request(
                $method,
                $this->getApiUrl($path),
                [
                    RequestOptions::JSON    => $data,
                    RequestOptions::HEADERS => [
                        'Authorization' => $token['token_type'] . ' ' . $token['access_token'],
                    ],
                ]
            );

            $responseCode = $response->getStatusCode();

        } else {
            $request->setHeader('Authorization', $token['token_type'] . ' ' . $token['access_token']);
            $request->setHeader('Content-Type', 'application/json');

            $request->send($this->getApiUrl($path), json_encode($data), $method);

            $responseCode = $request->code;
            $response     = $request;
        }

        // Token probably expired, try again with a new token
        if (401 === $responseCode && !$renewToken) {
            return $this->sendRequest($path, $data, $method, true);
        }

        return $response;
    }

    /**
     * @return Client|\RequestExtended
     */
    private function prepareRequest()
    {
        if (class_exists('GuzzleHttp\Client')) {
            $request = new Client(
                [
                    RequestOptions::TIMEOUT         => 5,
                    RequestOptions::CONNECT_TIMEOUT => 5,
                    RequestOptions::HTTP_ERRORS     => false,
                    RequestOptions::HEADERS         => [
                        'Accept'          => 'application/json',
                        'Accept-Language' => 'en_US',
                    ],
                ]
            );
        } else {
            $request = new \RequestExtended();
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('Accept-Language', 'en_US');
        }

        return $request;
    }

    private function getApiUrl($path)
    {
        return 'https://api.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/v1' . $path;
    }
}
