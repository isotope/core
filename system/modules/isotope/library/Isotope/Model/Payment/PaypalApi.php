<?php

/**
 * @noinspection PhpPossiblePolymorphicInvocationInspection
 * @noinspection UnsupportedStringOffsetOperationsInspection
 */

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Contao\Date;
use Contao\Environment;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @property string $paypal_client
 * @property string $paypal_secret
 */
abstract class PaypalApi extends Payment
{
    /**
     * @param IsotopePurchasableCollection $order
     *
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function createPayment(IsotopePurchasableCollection $order)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $row = [
                'name'  => strip_tags($item->name),
                'price' => number_format($item->getPrice(), 2),
                'currency' => $order->getCurrency(),
                'quantity' => $item->quantity,
            ];

            if ($item->sku) {
                $row['sku'] = $item->sku;
            }

            $items[] = $row;
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
                'return_url' => Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $order, null, true),
                'cancel_url' => Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true),
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

        $this->debugLog($data);

        return $this->sendRequest('/payments/payment', $data, 'POST');
    }

    /**
     * @param IsotopePurchasableCollection $order
     * @param string $paymentId
     *
     * @return ResponseInterface
     * @throws TransportExceptionInterface
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
     * @return ResponseInterface
     * @throws TransportExceptionInterface
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
        $paymentData = StringUtil::deserialize($collection->payment_data, true);
        $paymentData['PAYPAL'] = $paypalData;

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
        $paymentData = StringUtil::deserialize($collection->payment_data, true);

        return \array_key_exists('PAYPAL', $paymentData) ? $paymentData['PAYPAL'] : [];
    }

    /**
     * @param IsotopeProductCollection $collection
     * @param array                    $paypalData
     */
    protected function storeHistory(IsotopeProductCollection $collection, array $paypalData)
    {
        $paymentData = StringUtil::deserialize($collection->payment_data, true);

        if (!\is_array($paymentData['PAYPAL_HISTORY'])) {
            $paymentData['PAYPAL_HISTORY'] = [];
        }

        $paymentData['PAYPAL_HISTORY'][] = $paypalData;

        $collection->payment_data = $paymentData;
        $collection->save();
    }

    /**
     * {@inheritdoc}
     */
    public function backendInterface($orderId)
    {
        if (($objOrder = Order::findByPk($orderId)) === null) {
            return parent::backendInterface($orderId);
        }

        $arrPayment = StringUtil::deserialize($objOrder->payment_data, true);

        if (!\is_array($arrPayment['PAYPAL_HISTORY']) || empty($arrPayment['PAYPAL_HISTORY'])) {
            return parent::backendInterface($orderId);
        }

        $strBuffer = '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=payment', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>';

        foreach ($arrPayment['PAYPAL_HISTORY'] as $response) {
            if ($response['intent'] === 'sale'
                && $response['state'] === 'approved'
                && isset($response['transactions'][0]['related_resources'][0]['sale']['id'])
            ) {
                $saleId = $response['transactions'][0]['related_resources'][0]['sale']['id'];

                $strBuffer .= '
<div class="maintenance_inactive">
<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'][$this->type][0] . ')' . '</h2>
<div class="tl_tbox">
<p><strong>' . sprintf($GLOBALS['TL_LANG']['MSC']['paypalTransaction'], $saleId) . '</strong></p>
<p>' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionOnline'] .'</p>
<a class="tl_submit" href="https://www.paypal.com/activity/payment/' . $saleId . '" target="_blank">' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionButton'] . '</a>
</div>
</div>';

                break;
            }
        }

        foreach (array_reverse($arrPayment['PAYPAL_HISTORY']) as $transaction) {
            if (isset($transaction['create_time'])) {
                $dateCreated = Date::parse(
                    $GLOBALS['TL_CONFIG']['datimFormat'],
                    strtotime($transaction['create_time'])
                );
            } else {
                $dateCreated = '<i>UNKNOWN</i>';
            }

            $strBuffer .= '
<div class="maintenance_inactive">
<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['MSC']['paypalTransactionDetails'], $dateCreated) . '</h2>
<table class="tl_show">
  <tbody>
';

            $render = function($k, $v, &$i) use (&$strBuffer) {
                $strBuffer .= '
  <tr>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . ' style="width:auto"><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

                ++$i;
            };

            $loop = function($data, $loop, $i=0) use ($render, &$strBuffer) {
                foreach ($data as $k => $v) {
                    if (\in_array($k, ['potential_payer_info', 'links', 'create_time'], true)) {
                        continue;
                    }

                    if (\is_array($v)) {
                        $strBuffer .= '
  <tr>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . ' style="width:auto"><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '>
      <table class="tl_show" style="border:1px solid #d0d0d2; background:#fff"><tbody>';

                        $i++;
                        $loop($v, $loop, (int) $i % 2);

                        $strBuffer .= '</td></tbody></table></tr>';

                        continue;
                    }

                    $render($k, $v, $i);
                }
            };

            $loop($transaction, $loop);

            $strBuffer .= '
</tbody></table>
</div>';
        }

        return $strBuffer;
    }

    /**
     * @return array|null
     */
    private function getApiToken()
    {
        $client = $this->prepareClient();

        try {
            $response = $client->request('POST', $this->getApiUrl('/oauth2/token'), [
                'body' => ['grant_type' => 'client_credentials'],
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->paypal_client . ':' . $this->paypal_secret),
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                return null;
            }

            $content = $response->toArray();

            return \array_key_exists('access_token', $content) ? $content : null;
        } catch (TransportExceptionInterface $transportException) {
            $this->debugLog(sprintf(
                "PayPal API Error! (%s)",
                $transportException->getMessage()
            ));

            return null;
        }
    }

    /**
     * @param      $path
     * @param null $data
     * @param null $method
     * @param bool $renewToken
     *
     * @throws TransportExceptionInterface
     */
    private function sendRequest($path, array $data = null, $method = null, $renewToken = false): ResponseInterface
    {
        $client = $this->prepareClient();

        // TODO store and reuse token
        $token = $this->getApiToken();

        $response = $client->request($method, $this->getApiUrl($path), [
            'json' => $data,
            'headers' => [
                'Authorization' => $token['token_type'] . ' ' . $token['access_token'],
            ],
        ]);

        $responseCode = $response->getStatusCode();

        // Token probably expired, try again with a new token
        if (401 === $responseCode && !$renewToken) {
            return $this->sendRequest($path, $data, $method, true);
        }

        return $response;
    }

    private function prepareClient(): HttpClientInterface
    {
        return HttpClient::create([
            'max_duration' => 5,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
            ],
        ]);
    }

    private function getApiUrl($path): string
    {
        return 'https://api.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/v1' . $path;
    }
}
