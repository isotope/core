<?php
/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Contao\Request;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
use Isotope\Module\Checkout;
use Isotope\Template;

/**
 * Base class for Open Payment Platform
 *
 * @property string $opp_user_id
 * @property string $opp_password
 * @property string $opp_entity_id
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
        $brands = deserialize($this->opp_brands);

        if (!empty($brands)
            && is_array($brands)
            && (!static::supportsPaymentBrands($brands) || strlen(implode(' ', $brands)) > 32)
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
        $paymentBrands = deserialize($this->opp_brands, true);
        $supportedTypes = static::getPaymentTypes($paymentBrands);
        $paymentType = array_shift($supportedTypes);

        $base = $this->getBaseUrl();
        $request = $this->prepareRequest($paymentType, $objOrder);
        $request->send($base . '/v1/checkouts');

        $response = json_decode($request->response, true);
        $this->storeApiResponse($response, $objOrder);

        if ('000.200.100' !== $response['result']['code']) {
            \System::log(
                sprintf(
                    'Payment for order ID %s could not be initialized. See log files for more information.',
                    $objOrder->getId()
                ),
                __METHOD__,
                TL_ERROR
            );

            log_message(print_r($response, true), 'open_payment.log');

            Checkout::redirectToStep('failed');
        }

        /** @var Template|object $template */
        $template = new Template('iso_payment_opp');
        $template->base = $base;
        $template->action = Checkout::generateUrlForStep('complete', $objOrder);
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
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $ndc = \Input::get('id');

        $request = new Request();
        $request->send($this->getBaseUrl() . '/v1/checkouts/' . $ndc . '/payment');

        $response = json_decode($request->response, true);
        $this->storeApiResponse($response, $objOrder);

        if (!preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $response['result']['code'])
            || !in_array($response['paymentType'], static::$paymentTypes, true)
            || $ndc !== $response['ndc']
            || $objOrder->getTotal() != $response['amount']
            || $objOrder->getCurrency() != $response['currency']
        ) {
            \System::log(
                sprintf(
                    'Payment data for order ID %s could not be verified. See log files for more information.',
                    $objOrder->getId()
                ),
                __METHOD__,
                TL_ERROR
            );

            log_message(\Environment::get('request'), 'open_payment.log');
            log_message(print_r($response, true), 'open_payment.log');

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
            && in_array('CP', static::$paymentBrands[$response['paymentBrand']], true)
        ) {
            $request = $this->prepareRequest('CP', $objOrder);
            $request->send($this->getBaseUrl() . '/v1/payments/' . $response['id']);

            $response = json_decode($request->response, true);
            $this->storeApiResponse($response, $objOrder);

            if ('000.100.110' !== $response['result']['code']
                || 'CP' !== $response['paymentType']
                || $objOrder->getTotal() != $response['amount']
                || $objOrder->getCurrency() != $response['currency']
            ) {
                \System::log(
                    sprintf(
                        'Payment for order ID %s could not be captured. See log files for more information.',
                        $objOrder->getId()
                    ),
                    __METHOD__,
                    TL_ERROR
                );

                log_message(print_r($response, true), 'open_payment.log');

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
     * @param string                   $type
     * @param IsotopeProductCollection $objOrder
     *
     * @return Request
     */
    private function prepareRequest($type, IsotopeProductCollection $objOrder)
    {
        $params = [
            'authentication.userId'   => $this->opp_user_id,
            'authentication.password' => $this->opp_password,
            'authentication.entityId' => $this->opp_entity_id,
            'amount'                  => number_format($objOrder->getTotal(), 2, '.', ''),
            'currency'                => $objOrder->getCurrency(),
            'paymentType'             => $type
        ];

        $request = new Request();
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->method = 'post';
        $request->data = http_build_query($params);

        return $request;
    }

    /**
     * @param array                    $data
     * @param IsotopeProductCollection $objOrder
     */
    private function storeApiResponse(array $data, IsotopeProductCollection $objOrder)
    {
        $payments = deserialize($objOrder->payment_data, true);

        if (!is_array($payments['OPP'])) {
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

        return call_user_func_array('array_intersect', $types);
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
}
