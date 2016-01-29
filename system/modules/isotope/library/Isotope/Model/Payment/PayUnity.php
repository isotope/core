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
use Isotope\Model\ProductCollection\Order;


/**
 * Isotope payment method for www.payunity.com
 */
class PayUnity extends Postsale implements IsotopePayment
{

    /**
     * Process Instant Payment Notifications (IPN)
     *
     * @param IsotopeProductCollection $objOrder
     */
    public function processPostSale(IsotopeProductCollection $objOrder)
    {
        // TODO implement method
    }

    /**
     * Get the order object in a postsale request
     *
     * @return IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) \Input::post('IDENTIFICATION_TRANSACTIONID'));
    }

    /**
     * Return the checkout form.
     *
     * @param IsotopeProductCollection $objOrder  The order being places
     * @param \Module                  $objModule The checkout module instance
     *
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
    }
}
