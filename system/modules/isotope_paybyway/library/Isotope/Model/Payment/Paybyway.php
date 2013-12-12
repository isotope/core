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

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;


class Paybyway extends Payment implements IsotopePayment, IsotopePostsale
{

    /**
     * Paybyway only supports EUR currency
     * @return  bool
     */
    public function isAvailable()
    {
        $objConfig = Isotope::getConfig();

        if (null === $objConfig || $objConfig->currency != 'EUR') {
            return false;
        }

        return parent::isAvailable();
    }

    /**
     * Return the redirect form.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $objTemplate = new \Isotope\Template('iso_payment_paybyway');

        $objTemplate->action = 'https://www.paybyway.com/e-payments/pay';
        $objTemplate->headline      = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message       = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel        = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

        $objTemplate->merchant_id = (int) $this->paybyway_merchant_id;
        $objTemplate->amount = round($objOrder->getTotal() * 100);
        $objTemplate->currency = 'EUR';
        $objTemplate->order_number = $objOrder->id;
        $objTemplate->lang = ($GLOBALS['TL_LANGUAGE'] == 'fi' ? 'FI' : 'EN');
        $objTemplate->return_address = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->cancel_address = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;

        if ($this->debug) {
            $objTemplate->action = 'https://www.paybyway.com/e-payments/test_pay';
            $this->paybyway_private_key = 'private_key';
        }

        $objTemplate->authcode = strtoupper(md5(
            $this->paybyway_private_key . '|' .
            $objTemplate->merchant_id . '|' .
            $objTemplate->amount . '|' .
            $objTemplate->currency . '|' .
            $objTemplate->order_number . '|' .
            $objTemplate->lang . '|' .
            $objTemplate->return_address . '|' .
            $objTemplate->cancel_address
        ));

        return $objTemplate->parse();
    }

    /**
     * Process payment on checkout page.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if ($objOrder->isLocked()) {
            return true;
        }

        return false;
    }


    /**
     * Process post-sale requestion from the PSP payment server.
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        var_dump($_POST);
    }


    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('ORDER_NUMBER'));
    }
}
