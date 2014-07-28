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

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;


class EPay extends Postsale implements IsotopePayment
{

    /**
     * Process ePay callback
     *
     * @param IsotopeProductCollection $objOrder
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        $arrValues = $_GET;
        unset($arrValues['hash']);

        if (md5(implode('', $arrValues) . $this->epay_secretkey) != \Input::get('hash')) {
            \System::log('Invalid hash for ePay payment', __METHOD__, TL_ERROR);
            return;
        }

        if ($objOrder->currency != \Input::get('currency') || ($objOrder->getTotal * 100) != \Input::get('amount')) {
            \System::log('Currency or amount does not match order', __METHOD__, TL_ERROR);
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

    /**
     * Get the order object in a postsale request
     *
     * @return  IsotopeProductCollection|null
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('orderid'));
    }

    /**
     * Return the ePay form
     *
     * @param IsotopeProductCollection $objOrder
     * @param \Module                  $objModule
     *
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $objTemplate = new \Isotope\Template('iso_payment_epay');
        $objTemplate->setData($this->arrData);

        $objTemplate->currency = $objOrder->currency;
        $objTemplate->amount = $objOrder->getTotal() * 100;
        $objTemplate->orderid = $objOrder->id;
        $objTemplate->instantcapture = ($this->trans_type == 'capture' ? '1' : '0');
        $objTemplate->callbackurl = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->accepturl = \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder);
        $objTemplate->cancelurl = \Environment::get('base') . $objModule->generateUrlForStep('failed');

        $objTemplate->calculateHash = function(array $arrField) use ($objTemplate) {
            return '';
        };

        return $objTemplate->parse();
    }
}
