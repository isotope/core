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
use Contao\StringUtil;
use Contao\System;
use Haste\DateTime\DateTime;
use Isotope\Currency;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class VADS
 *
 * @property string $vads_site_id
 * @property string $vads_certificate
 */
abstract class VADS extends Postsale
{
    /**
     * List of parameters to validate on inbound data
     * @var array
     */
    protected $inboundParameters = array(
        'vads_action_mode',
        'vads_amount',
        'vads_ctx_mode',
        'vads_currency',
        'vads_payment_config',
        'vads_site_id',
        'vads_trans_id',
        'vads_version',
        'vads_order_id',
        'vads_cust_id',
    );

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        // Verify payment status
        if (Input::post('vads_result') != '00') {
            System::log('Payment for order ID "' . $objOrder->getId() . '" failed.', __METHOD__, TL_ERROR);
            return;
        }

        // Validate HMAC sign
        if (Input::post('signature') != $this->calculateSignature($_POST, $this->vads_certificate)) {
            System::log('Invalid signature for Order ID ' . $objOrder->getId(), __METHOD__, TL_ERROR);
            return;
        }

        // For maximum security, also validate individual parameters
        if (!$this->validateInboundParameters($objOrder)) {
            System::log('Parameter mismatch for Order ID ' . $objOrder->getId(), __METHOD__, TL_ERROR);
            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return;
        }

        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(Input::post('vads_order_id'));
    }

    /**
     * Generate the submit form for Innopay and if javascript is enabled redirect automatically
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $parameters = $this->getOutboundParameters($objOrder);
        $parameters['signature'] = $this->calculateSignature($parameters, $this->vads_certificate);

        /** @var Template|\stdClass $objTemplate */
        $objTemplate           = new Template($this->strTemplate);
        $objTemplate->id       = $this->id;
        $objTemplate->params   = $parameters;
        $objTemplate->headline = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * @param IsotopePurchasableCollection $objOrder
     *
     * @return array
     */
    protected function getOutboundParameters(IsotopePurchasableCollection $objOrder)
    {
        $objAddress = $objOrder->getBillingAddress();
        $successUrl = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true);
        $failureUrl = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true);

        $transDate  = new DateTime();
        $transDate->setTimezone(new \DateTimeZone('UTC'));

        return array(
            'vads_action_mode'    => 'INTERACTIVE',
            'vads_amount'         => Currency::getAmountInMinorUnits($objOrder->getTotal(), $objOrder->getCurrency()),
            'vads_contrib'        => 'Isotope eCommerce ' . Isotope::VERSION,
            'vads_ctx_mode'       => $this->debug ? 'TEST' : 'PRODUCTION',
            'vads_currency'       => Currency::getIsoNumber($objOrder->getCurrency()),
            'vads_cust_address'   => $objAddress->street_1,
            'vads_cust_city'      => $objAddress->city,
            'vads_cust_country'   => $objAddress->country,
            'vads_cust_email'     => $objAddress->email,
            'vads_cust_id'        => null === $objOrder->getMember() ? 0 : $objOrder->getMember()->id,
            'vads_cust_name'      => $objAddress->firstname . ' ' . $objAddress->lastname,
            'vads_cust_phone'     => $objAddress->phone,
            'vads_cust_title'     => $objAddress->salutation,
            'vads_cust_zip'       => $objAddress->postal,
            'vads_language'       => $objOrder->language,
            'vads_order_id'       => $objOrder->getId(),
            'vads_page_action'    => 'PAYMENT',
            'vads_payment_config' => 'SINGLE',
            'vads_return_mode'    => 'NONE',
            'vads_site_id'        => $this->vads_site_id,
            'vads_trans_date'     => $transDate->format('YmdHis'),
            'vads_trans_id'       => str_pad($objOrder->getId(), 6, '0', STR_PAD_LEFT),
            'vads_url_cancel'     => $failureUrl,
            'vads_url_check'      => System::getContainer()->get('router')->generate('isotope_postsale', ['mod' => 'pay', 'id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL),
            'vads_url_error'      => $failureUrl,
            'vads_url_referral'   => $failureUrl,
            'vads_url_refused'    => $failureUrl,
            'vads_url_success'    => $successUrl,
            'vads_url_return'     => $failureUrl,
            'vads_version'        => 'V2',
        );
    }

    /**
     * Calculate SHA1 signature for the payment parameters
     *
     * @param array  $parameters
     * @param string $certificate
     *
     * @return string
     */
    protected function calculateSignature(array $parameters, $certificate)
    {
        // Remove all parameters that do not start with "vads_"
        foreach ($parameters as $k => $v) {
            if (strpos($k, 'vads_') !== 0) {
                unset($parameters[$k]);
            }
        }

        ksort($parameters);

        $values = implode('+', $parameters);

        return hash('sha1', $values . '+' . $certificate);
    }

    /**
     * Validate input parameters to prevent payment manipulation
     *
     * @param IsotopePurchasableCollection $objOrder
     *
     * @return bool
     */
    protected function validateInboundParameters(IsotopePurchasableCollection $objOrder)
    {
        $parameters = $this->getOutboundParameters($objOrder);

        foreach ($this->inboundParameters as $key) {
            if ($parameters[$key] != Input::post($key)) {
                return false;
            }
        }

        return true;
    }
}
