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

use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\System;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Mpay24\Mpay24Config;
use Mpay24\Mpay24Order;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * mPAY24 payment method
 *
 * @property string $mpay24_merchant
 * @property string $mpay24_password
 */
class Mpay24 extends Postsale
{
    /**
     * Perform server to server data check
     *
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objOrder->getId() . '" is not purchasable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            return;
        }

        $mpay24 = $this->getApiClient();
        $status = $mpay24->paymentStatus(Input::get('MPAYTID'));

        $this->debugLog($status);

        if ($status->getParam('STATUS') !== 'BILLED') {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Payment for order ID "' . $objOrder->getId() . '" failed.', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return;
        }

        if (!$objOrder->checkout()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Postsale checkout for Order ID "' . Input::post('refno') . '" failed', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return;
        }

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        die('OK');
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(Input::get('TID'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objOrder->getId() . '" is not purchasable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return false;
        }

        $mpay24 = $this->getApiClient();

        $mdxi = new Mpay24Order();
        $mdxi->Order->Tid = $objOrder->getId();
        $mdxi->Order->Price = $objOrder->getTotal();

        if ($billingAddress = $objOrder->getBillingAddress()) {
            $this->setAddress($mdxi, $billingAddress, 'BillingAddr');
        }

        if (($shippingAddress = $objOrder->getShippingAddress()) && $shippingAddress->id !== $billingAddress->id) {
            $this->setAddress($mdxi, $shippingAddress, 'ShippingAddr');
        }

        $mdxi->Order->URL->Success = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true);
        $mdxi->Order->URL->Error = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true);
        $mdxi->Order->URL->Confirmation = System::getContainer()->get('router')->generate('isotope_postsale', ['mod' => 'pay', 'id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);

        $template = new FrontendTemplate('iso_payment_mpay24');
        $template->setData($this->row());
        $template->mpay24 = $mpay24;
        $template->mdxi = $mdxi;

        // BC with Isotope <= 2.6.10
        $template->location = function() use ($mpay24, $mdxi) {
            return $mpay24->paymentPage($mdxi)->getLocation();
        };

        return $template->parse();
    }

    /**
     * @return \Mpay24\Mpay24
     */
    private function getApiClient()
    {
        $config = new Mpay24Config(
            $this->mpay24_merchant,
            $this->mpay24_password,
            $this->debug,
            $this->logging
        );

        $config->setLogPath(\Contao\System::getContainer()->getParameter('kernel.project_dir') . '/system/logs');
        $config->setLogFile('isotope_mpay24.log');

        return new \Mpay24\Mpay24($config);
    }

    private function setAddress(Mpay24Order $mdxi, Address $address, $addressKey)
    {
        $mdxi->Order->{$addressKey}->setMode('ReadOnly');
        $mdxi->Order->{$addressKey}->Name = $address->firstname.' '.$address->lastname;
        $mdxi->Order->{$addressKey}->Street = $address->street_1;
        $mdxi->Order->{$addressKey}->Zip = $address->postal;
        $mdxi->Order->{$addressKey}->City = $address->city;
        $mdxi->Order->{$addressKey}->Country->setCode(strtoupper($address->country));
        $mdxi->Order->{$addressKey}->Email = $address->email;
        $mdxi->Order->{$addressKey}->Phone = $address->phone;
    }
}
