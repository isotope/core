<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;


/**
 * Class PaymentCash
 *
 * Handle cash payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Cash extends Payment implements IsotopePayment
{

    /**
     * processPayment function.
     *
     * @access public
     * @return boolean
     */
    public function processPayment()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null) {
            return false;
        }

        $objOrder->updateOrderStatus($this->new_order_status);

        return true;
    }
}
