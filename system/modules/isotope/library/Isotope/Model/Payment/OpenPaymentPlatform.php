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

use Contao\Environment;
use Contao\Input;
use Contao\Module;
use Contao\Request;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Address;
use Isotope\Model\Payment;
use Isotope\Module\Checkout;
use Isotope\Template;

/**
 * Base class for Open Payment Platform
 *
 * @property string $opp_entity_id
 * @property string $opp_auth
 * @property string $opp_token
 * @property string $opp_user_id
 * @property string $opp_password
 * @property array  $opp_brands
 */
class OpenPaymentPlatform extends Payment
{
    public static $paymentTypes = ['PA', 'CP', 'DB'];

    public static $paymentBrands = [
        'AMEX' => ['PA', 'CP', 'DB'],
        'DINERS' => ['PA', 'CP', 'DB'],
        'DIRECTDEBIT_SEPA' => ['PA', 'CP', 'DB'],
        'GIROPAY' => ['PA'],
        'JCB' => ['PA', 'CP', 'DB'],
        'KLARNA_INSTALLMENTS' => ['PA', 'CP'],
        'KLARNA_INVOICE' => ['PA', 'CP'],
        'MASTER' => ['PA', 'CP', 'DB'],
        'PAYDIREKT' => ['PA', 'CP', 'DB'],
        'PAYPAL' => ['PA', 'CP', 'DB'],
        'RATENKAUF' => ['PA', 'CP'],
        'SOFORTUEBERWEISUNG' => ['DB'],
        'VISA' => ['PA', 'CP', 'DB'],
    ];

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        $brands = StringUtil::deserialize($this->opp_brands);

        if (!empty($brands)
            && \is_array($brands)
            && (!static::supportsPaymentBrands($brands) || \strlen(implode(' ', $brands)) > 32)
        ) {
            return false;
        }

        return parent::isAvailable();
    }


    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $paymentBrands = StringUtil::deserialize($this->opp_brands, true);
        $supportedTypes = static::getPaymentTypes($paymentBrands);
        $paymentType = array_shift($supportedTypes);

        $base = $this->getBaseUrl();
        $request = $this->createPostRequest($paymentType, $objOrder, $this->preparePaymentParams($objOrder));
        $request->send($base . '/v1/checkouts');

        $response = json_decode($request->response, true);
        $this->storeApiResponse($response, $objOrder);

        $this->debugLog($response);

        if ('000.200.100' !== $response['result']['code']) {
            System::log(
                sprintf(
                    'Payment for order ID %s could not be initialized. See log files for more information.',
                    $objOrder->getId()
                ),
                __METHOD__,
                TL_ERROR
            );

            Checkout::redirectToStep('failed');
        }

        /** @var Template|object $template */
        $template = new Template('iso_payment_opp');
        $template->base = $base;
        $template->action = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true);
        $template->checkoutId = $response['id'];
        $template->brands = '';

        if (!empty($paymentBrands)) {
            $template->brands = implode(' ', $paymentBrands);
        }

        return $template->parse();
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

        $ndc = Input::get('id');

        $request = new Request();
        $url = $this->getBaseUrl() . '/v1/checkouts/' . $ndc . '/payment';

        if ('token' === $this->opp_auth) {
            $url .= '?entityId='.$this->opp_entity_id;
            $request->setHeader('Authorization', 'Bearer '.$this->opp_token);
        }

        $request->send($url);

        $response = json_decode($request->response, true);
        $this->storeApiResponse($response, $objOrder);

        $this->debugLog(Environment::get('request'));
        $this->debugLog($response);

        if (!preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $response['result']['code'])
            || !\in_array($response['paymentType'], static::$paymentTypes, true)
            || $ndc !== $response['ndc']
            || $objOrder->getTotal() != $response['amount']
            || $objOrder->getCurrency() != $response['currency']
        ) {
            System::log(
                sprintf(
                    'Payment data for order ID %s could not be verified. See log files for more information.',
                    $objOrder->getId()
                ),
                __METHOD__,
                TL_ERROR
            );

            return false;
        }

        // Debit request is always paid
        if ('DB' === $response['paymentType']) {
            $objOrder->setDatePaid(time());
            $objOrder->updateOrderStatus($this->new_order_status);
            $objOrder->save();

            return true;
        }

        // Capture payment
        if ('capture' === $this->trans_type
            && 'PA' === $response['paymentType']
            && isset(static::$paymentBrands[$response['paymentBrand']])
            && \in_array('CP', static::$paymentBrands[$response['paymentBrand']], true)
        ) {
            $request = $this->createPostRequest('CP', $objOrder);
            $request->send($this->getBaseUrl() . '/v1/payments/' . $response['id']);

            $response = json_decode($request->response, true);
            $this->storeApiResponse($response, $objOrder);

            $this->debugLog($response);

            if (!preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $response['result']['code'])
                || 'CP' !== $response['paymentType']
                || $objOrder->getTotal() != $response['amount']
                || $objOrder->getCurrency() != $response['currency']
            ) {
                System::log(
                    sprintf(
                        'Payment for order ID %s could not be captured. See log files for more information.',
                        $objOrder->getId()
                    ),
                    __METHOD__,
                    TL_ERROR
                );

                return false;
            }

            $objOrder->setDatePaid(time());
            $objOrder->updateOrderStatus($this->new_order_status);
        }

        return true;
    }

    private function getBaseUrl()
    {
        return 'https://' . ($this->debug ? 'test.' : '') . 'oppwa.com';
    }

    /**
     * @param string                       $paymentType
     * @param IsotopePurchasableCollection $objOrder
     * @param array                        $params
     *
     * @return Request
     */
    private function createPostRequest($paymentType, IsotopePurchasableCollection $objOrder, array $params = [])
    {
        $request = new Request();
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        if ('token' === $this->opp_auth) {
            $params['entityId'] = $this->opp_entity_id;
            $request->setHeader('Authorization', 'Bearer '.$this->opp_token);
        } else {
            $params['authentication.entityId'] = $this->opp_entity_id;
            $params['authentication.userId']   = $this->opp_user_id;
            $params['authentication.password'] = $this->opp_password;
        }

        $params['amount'] = number_format($objOrder->getTotal(), 2, '.', '');
        $params['currency'] = $objOrder->getCurrency();
        $params['paymentType'] = $paymentType;

        $request->method = 'post';
        $request->data = http_build_query($params);

        return $request;
    }

    /**
     * @param array                        $data
     * @param IsotopePurchasableCollection $objOrder
     */
    private function storeApiResponse(array $data, IsotopePurchasableCollection $objOrder)
    {
        $payments = StringUtil::deserialize($objOrder->payment_data, true);

        if (!\is_array($payments['OPP'])) {
            $payments['OPP'] = array();
        }

        $payments['OPP'][] = $data;

        $objOrder->payment_data = $payments;
        $objOrder->save();
    }

    /**
     * @param array $brands
     *
     * @return array
     */
    public static function getPaymentTypes(array $brands)
    {
        if (empty($brands)) {
            return static::$paymentTypes;
        }

        $types = array_values(array_intersect_key(static::$paymentBrands, array_flip($brands)));
        array_unshift($types, static::$paymentTypes);

        return array_intersect(...$types);
    }

    /**
     * @param mixed $brands
     *
     * @return bool
     */
    public static function supportsPaymentBrands(array $brands)
    {
        $types = static::getPaymentTypes($brands);

        return !empty($types);
    }

    /**
     * @param IsotopePurchasableCollection $objOrder
     *
     * @return array
     */
    private function preparePaymentParams(IsotopePurchasableCollection $objOrder)
    {
        $params = [];
        $params['merchantTransactionId'] = str_pad($objOrder->getId(), '0', STR_PAD_LEFT);
        $params['transactionCategory'] = 'EC';

        if (null !== $objOrder->getMember()) {
            $params['customer.merchantCustomerId'] = $objOrder->getMember()->id;
        }

        if (null !== ($billingAddress = $objOrder->getBillingAddress())) {
            $this->setCustomerParams($params, $billingAddress, 'customer');
            $this->setAddressParams($params, $billingAddress, 'billing');
        }

        if ($objOrder->hasShipping() && null !== ($shippingAddress = $objOrder->getShippingAddress())) {
            $this->setCustomerParams($params, $shippingAddress, 'shipping.customer');
            $this->setAddressParams($params, $shippingAddress, 'shipping');
        }

        return $params;
    }

    /**
     * @param array   $params
     * @param Address $address
     * @param string  $type
     */
    private function setCustomerParams(array &$params, Address $address, $type)
    {
        $params[$type.'.givenName'] = $address->firstname;
        $params[$type.'.surname'] = $address->lastname;

        if ($address->gender === 'male') {
            $params[$type.'.sex'] = 'M';
        } elseif ($address->gender === 'female') {
            $params[$type.'.sex'] = 'F';
        }

        if ($address->dateOfBirth) {
            $params[$type.'.birthDate'] = date('Y-m-d', $address->dateOfBirth);
        }

        if ($address->phone) {
            $params[$type.'.phone'] = $address->phone;
        }

        if ($address->email) {
            $params[$type.'.email'] = $address->email;
        }

        if ($address->company) {
            $params[$type.'.companyName'] = $address->company;
        }
    }

    /**
     * @param array   $params
     * @param Address $address
     * @param string  $type
     */
    private function setAddressParams(array &$params, Address $address, $type)
    {
        $params[$type.'.street1'] = $address->street_1;

        if ($address->street_2) {
            $params[$type.'.street2'] = $address->street_2;
        }

        $params[$type.'.city'] = $address->city;
        $params[$type.'.postcode'] = $address->postal;
        $params[$type.'.country'] = strtoupper($address->country);
    }
}
