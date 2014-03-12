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
            $this->paybyway_private_key .
            '|' . $objTemplate->merchant_id .
            '|' . $objTemplate->amount .
            '|' . $objTemplate->currency .
            '|' . $objTemplate->order_number .
            '|' . $objTemplate->lang .
            '|' . $objTemplate->return_address .
            '|' . $objTemplate->cancel_address
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
        if ($this->debug) {
            $this->paybyway_private_key = 'private_key';
        }

        $strChecksum = strtoupper(md5(
            $this->paybyway_private_key .
            '|' . \Input::post('RETURN_CODE') .
            '|' . \Input::post('ORDER_NUMBER') .
            (\Input::post('SETTLED') ? ('|' . \Input::post('SETTLED')) : '') .
            (\Input::post('INCIDENT_ID') ? ('|' . \Input::post('INCIDENT_ID')) : '')
        ));

        if (\Input::post('AUTHCODE') != $strChecksum) {
            \System::log('Postsale manipulation for order ID ' . $objOrder->id, __METHOD__, TL_ERROR);
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        switch (\Input::post('RETURN_CODE')) {

            case 0: // Payment completed successfully.
                if ($objOrder->checkout()) {
                    $objOrder->date_paid = time();
                    $objOrder->updateOrderStatus($this->new_order_status);
                    \Isotope\Module\Checkout::redirectToStep('complete', $objOrder);
                }
                break;

            case 4: // Transaction status could not be updated after customer returned from the web page of a bank. Please use the merchant UI to resolve the payment status.
                if (($objConfig = $objOrder->getRelated('config_id')) === null) {
                    \System::log('Config for Order ID ' . $objOrder->id . ' not found', __METHOD__, TL_ERROR);

                } elseif ($objOrder->checkout()) {
                    $objOrder->updateOrderStatus($objConfig->orderstatus_error);
                    \Isotope\Module\Checkout::redirectToStep('complete', $objOrder);
                }
                break;

            case 1: // Payment failed. Customer did not successfully finish the payment.
            case 2: // Duplicate order number. You have reused an order number. Make sure that your order numbers are unique, and are not reused in any case.
            case 3: // User disabled. Either your Paybyway account has been temporarily disabled for security reasons, or your sub-merchant is disabled.
            case 10: // Maintenance break. The transaction is not created and the user has been notified and transferred back to the cancel address.
                // Do nothing here, we redirect to "failed" by default
                break;
        }

        \System::log('Paybyway checkout failed for order ID ' . $objOrder->id, __METHOD__, TL_ERROR);

        \Isotope\Module\Checkout::redirectToStep('failed');
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
