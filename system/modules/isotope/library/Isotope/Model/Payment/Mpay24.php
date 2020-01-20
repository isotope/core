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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Mpay24\Mpay24Config;
use Mpay24\Mpay24Order;

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
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            return;
        }

        $mpay24 = $this->getApiClient();
        $status = $mpay24->paymentStatus(\Input::get('MPAYTID'));

        $this->debugLog($status);

        if ($status->getParam('STATUS') !== 'BILLED') {
            \System::log('Payment for order ID "' . $objOrder->getId() . '" failed.', __METHOD__, TL_ERROR);

            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            \System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return;
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('refno') . '" failed', __METHOD__, TL_ERROR);

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
        return Order::findByPk(\Input::get('TID'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $mpay24 = $this->getApiClient();

        $mdxi = new Mpay24Order();
        $mdxi->Order->Tid = $objOrder->getId();
        $mdxi->Order->Price = $objOrder->getTotal();
        $mdxi->Order->URL->Success = \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder);
        $mdxi->Order->URL->Error = \Environment::get('base') . Checkout::generateUrlForStep('failed');
        $mdxi->Order->URL->Confirmation = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;

        $template = new \FrontendTemplate('iso_payment_mpay24');
        $template->setData($this->row());
        $template->location = $mpay24->paymentPage($mdxi)->getLocation();

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

        $config->setLogPath(TL_ROOT . '/system/logs');
        $config->setLogFile('isotope_mpay24.log');

        return new \Mpay24\Mpay24($config);
    }
}
