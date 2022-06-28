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
use Contao\System;
use Isotope\Currency;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * EPay payment method
 *
 * @property string $epay_windowstate
 * @property string $epay_merchantnumber
 * @property string $epay_secretkey
 */
class EPay extends Payment implements IsotopePostsale
{

    /**
     * ePay language IDs
     */
    private static $arrLanguages = array(
        'da' => 1,  // Danish
        'en' => 2,  // English
        'sv' => 3,  // Swedish
        'no' => 4,  // Norwegian
        'kl' => 5,  // Greenlandic
        'is' => 6,  // Icelandic
        'de' => 7,  // German
        'fi' => 8,  // Finnish
        'es' => 9,  // Spanish
        'fr' => 10, // French
        'pl' => 11, // Polish
        'it' => 12, // Italian
        'nl' => 13, // Dutch
    );

    /**
     * Check the cart currency for ePay support
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (!Currency::isSupported(Isotope::getConfig()->currency)) {
            return false;
        }

        return parent::isAvailable();
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

        if (!$this->validatePayment($objOrder)) {
            return false;
        }

        if (!$objOrder->checkout()) {
            System::log('Checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);
            return false;
        }

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        return true;
    }

    /**
     * Process ePay callback
     *
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        if (!$this->validatePayment($objOrder)) {
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
        return Order::findByPk((int) Input::get('orderid'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_epay');
        $objTemplate->setData($this->arrData);

        $objTemplate->currency       = $objOrder->getCurrency();
        $objTemplate->amount         = Currency::getAmountInMinorUnits($objOrder->getTotal(), $objOrder->getCurrency());
        $objTemplate->orderid        = $objOrder->getId();
        $objTemplate->instantcapture = 'capture' === $this->trans_type ? '1' : '0';
        $objTemplate->callbackurl    = System::getContainer()->get('router')->generate('isotope_postsale', ['mod' => 'pay', 'id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);
        $objTemplate->accepturl      = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true);
        $objTemplate->cancelurl      = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true);
        $objTemplate->language       = (int) static::$arrLanguages[substr($GLOBALS['TL_LANGUAGE'], 0, 2)];

        return $objTemplate->parse();
    }

    /**
     * Validate input parameters and hash
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return bool
     */
    protected function validatePayment(IsotopeProductCollection $objOrder)
    {
        $arrValues = $_GET;

        unset(
            $arrValues['hash'],
            $arrValues['auto_item'],
            $arrValues['step']
        );

        $strHash       = md5(implode('', $arrValues) . $this->epay_secretkey);
        $orderCurrency = $objOrder->getCurrency();
        $orderAmount   = Currency::getAmountInMinorUnits($objOrder->getTotal(), $orderCurrency);

        if ($strHash != Input::get('hash')) {
            System::log('Invalid hash for ePay payment. See system/logs/isotope_epay.log for more details.', __METHOD__, TL_ERROR);

            $this->debugLog(
                sprintf(
                    "Invalid hash for ePay payment:\ngot %s, expected %s\nParameters: %s\n\n",
                    Input::get('hash'),
                    $strHash,
                    print_r($arrValues, true)
                )
            );

            return false;
        }

        if (Currency::getIsoNumber($orderCurrency) != Input::get('currency')
            || $orderAmount != Input::get('amount')
        ) {
            System::log('Currency or amount does not match order.  See system/logs/isotope_epay.log for more details.', __METHOD__, TL_ERROR);

            $this->debugLog(
                sprintf(
                    "Currency or amount does not match order:\nCurrency: got %s (%s), expected %s\nAmount: got %s, expected %s\n\n",
                    Input::get('currency'),
                    Currency::getIsoNumber($orderCurrency),
                    $orderCurrency,
                    Input::get('amount'),
                    $orderAmount
                )
            );

            return false;
        }

        return true;
    }
}
