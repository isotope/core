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

use Isotope\Currency;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;

/**
 * Class QuickPay
 *
 * @property int    $quickpay_merchantId
 * @property int    $quickpay_agreementId
 * @property string $quickpay_apiKey
 * @property string $quickpay_privateKey
 * @property string $quickpay_paymentMethods
 */
class QuickPay extends Postsale
{
    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if ($this->validatePayment($objOrder)) {
            if ($objOrder->isCheckoutComplete()) {
                return;
            }

            if (!$objOrder->checkout()) {
                \System::log('Postsale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                return;
            }

            $objOrder->date_paid = time();
            $objOrder->updateOrderStatus($this->new_order_status);

            $objOrder->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        $data = $this->getRequestResource();

        if (null === $data) {
            return null;
        }

        return Order::findByPk((int) $data['order_id']);
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $objTemplate = new Template('iso_payment_quickpay');
        $objTemplate->setData($this->arrData);

        $params = array(
            'version'      => 'v10',
            'merchant_id'  => $this->quickpay_merchantId,
            'agreement_id' => $this->quickpay_agreementId,
            'order_id'     => str_pad($objOrder->id, 4, '0', STR_PAD_LEFT),
            'language'     => substr($GLOBALS['TL_LANGUAGE'], 0, 2),
            'amount'       => Currency::getAmountInMinorUnits($objOrder->getTotal(), $objOrder->getCurrency()),
            'currency'     => $objOrder->currency,
            'continueurl'  => \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder),
            'cancelurl'    => \Environment::get('base') . Checkout::generateUrlForStep('failed'),
            'callbackurl'  => \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id,
            'autocapture'  => 'capture' === $this->trans_type ? '1' : '0',
        );

        if ('' !== $this->quickpay_paymentMethods) {
            $params['payment_methods'] = $this->quickpay_paymentMethods;
        }

        $apiKey = $this->quickpay_apiKey;

        $objTemplate->params = $params;
        $objTemplate->calculateHash = function($params) use ($apiKey) {
            ksort($params);

            return hash_hmac("sha256", implode(" ", $params), $apiKey);
        };

        return $objTemplate->parse();
    }

    /**
     * Validate input parameters and hash
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return bool
     */
    private function validatePayment(IsotopeProductCollection $objOrder)
    {
        $checksum = hash_hmac("sha256", file_get_contents("php://input"), $this->quickpay_privateKey);

        if ($checksum != $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256']) {
            \System::log(
                'Invalid hash for QuickPay payment. See system/logs/isotope_quickpay.log for more details.',
                __METHOD__,
                TL_ERROR
            );

            log_message(
                sprintf(
                    "Invalid hash for QuickPay payment:\ngot %s, expected %s\nInput: %s\n\n",
                    $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256'],
                    $checksum,
                    file_get_contents("php://input")
                ),
                'isotope_quickpay.log'
            );

            return false;
        }

        $data = $this->getRequestResource();

        if (null === $data) {
            return false;
        }

        $amount = Currency::getAmountInMinorUnits($objOrder->getTotal(), $objOrder->currency);

        if ($objOrder->currency != $data['currency']
            || $amount != $data['operations'][0]['amount']
            || 0 != $data['balance']
            || $data['test_mode'] != $this->debug
        ) {
            \System::log(
                'QuickPay data was not accepted. See system/logs/isotope_quickpay.log for more details.',
                __METHOD__,
                TL_ERROR
            );

            log_message(
                sprintf(
                    "QuickPay data was not accepted:\n" .
                    "Currency: got \"%s\", expected \"%s\"\n" .
                    "Amount: got \"%s\", expected \"%s\"\n" .
                    "Balance: got \"%s\", expected \"0\"\n" .
                    "Accepted: got \"%s\", expected \"yes\"\n\n" .
                    "Test Mode: got \"%s\", expected \"%s\"\n\n",
                    $data['currency'],
                    $objOrder->currency,
                    $data['operations'][0]['amount'],
                    $amount,
                    $data['balance'],
                    ($data['accepted'] ? 'yes' : 'no'),
                    ($data['test_mode'] ? 'yes' : 'no'),
                    ($this->debug ? 'yes' : 'no')
                ),
                'isotope_quickpay.log'
            );

            return false;
        }

        return true;
    }

    /**
     * Parse the request body and return the resource from JSON.
     *
     * @return array|null The resource or NULL if JSON is invalid
     */
    private function getRequestResource()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (null === $data) {
            \System::log(
                'Unable to read JSON for QuickPay payment. See system/logs/isotope_quickpay.log for more details.',
                __METHOD__,
                TL_ERROR
            );

            log_message(
                sprintf(
                    "Unable to read JSON for QuickPay payment:\nInput: %s\n\n",
                    file_get_contents("php://input")
                ),
                'isotope_quickpay.log'
            );

            return null;
        }

        return $data;
    }
}
