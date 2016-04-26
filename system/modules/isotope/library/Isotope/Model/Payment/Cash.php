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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;


/**
 * Class PaymentCash
 *
 * Handle cash payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Cash extends Payment
{
    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $objOrder->checkout();
        $objOrder->updateOrderStatus($this->new_order_status);

        return true;
    }
}
