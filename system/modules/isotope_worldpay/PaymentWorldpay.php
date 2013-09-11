<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Isotope payment method for www.worldpay.com
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class PaymentWorldpay extends IsotopePayment
{

    /**
     * processPayment function.
     *
     * @access public
     * @return void
     */
    public function processPayment()
    {
        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
        {
            return false;
        }

        if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
        {
            IsotopeFrontend::clearTimeout();
            return true;
        }

        if (IsotopeFrontend::setTimeout())
        {
            // Do not index or cache the page
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate = new FrontendTemplate('mod_message');
            $objTemplate->type = 'processing';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
            return $objTemplate->parse();
        }

        $this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
        $this->redirect($this->addToUrl('step=failed', true));
    }


    /**
     * Process PayPal Instant Payment Notifications (IPN)
     *
     * @access public
     * @return void
     */
    public function processPostSale()
    {
        if ($this->Input->post('instId') != $this->worldpay_instId) {
            $this->log('Installation ID does not match', __METHOD__, TL_ERROR);
            return;
        }

        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('cart_id', $this->Input->post('cartId'))) {
            $this->log('Order ID "' . $this->Input->post('cartId') . '" not found', __METHOD__, TL_ERROR);
            return;
        }

        // Validate payment data (see #2221)
        if ($objOrder->currency != $this->Input->post('currency') || $objOrder->grandTotal != $this->Input->post('amount')) {
            $this->log('Data manipulation in payment from "' . $this->Input->post('email') . '" !', __METHOD__, TL_ERROR);
            return;
        }

        // Order status cancelled and order not yet completed, do nothing
        if ($this->Input->get('transStatus') != 'Y' && $objOrder->status == 0) {
            return;
        }

        if ($this->Input->get('transStatus') == 'Y') {
            if (!$objOrder->checkout()) {
                $this->log('Checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                return;
            }

            $objOrder->date_paid = time();
        }

        // Store request data in order for future references
        $arrPayment = deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data = $arrPayment;

        $objOrder->save();
    }


    /**
     * Return the PayPal form.
     *
     * @access public
     * @return string
     */
    public function checkoutForm()
    {
        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id)) {
            $this->redirect($this->addToUrl('step=failed', true));
        }

        $objAddress = $this->Isotope->Cart->billingAddress;

        $objTemplate = new IsotopeTemplate('iso_payment_worldpay');
        $objTemplate->instId = $this->worldpay_instId;
        $objTemplate->cartId = $this->Isotope->Cart->id;
        $objTemplate->amount = $this->Isotope->Cart->grandTotal;
        $objTemplate->currency = $this->Isotope->Config->currency;
        $objTemplate->description = $this->worldpay_description;
        $objTemplate->name = substr($objAddress->firstname . ' ' . $objAddress->lastname, 0, 40);

        if ($objAddress->company != '') {
            $objTemplate->address1 = substr($objAddress->company, 0, 84);
            $objTemplate->address2 = substr($objAddress->street_1, 0, 84);
            $objTemplate->address3 = substr($objAddress->street_2, 0, 84);
        } else {
            $objTemplate->address1 = substr($objAddress->street_1, 0, 84);
            $objTemplate->address2 = substr($objAddress->street_2, 0, 84);
            $objTemplate->address3 = substr($objAddress->street_3, 0, 84);
        }

        $objTemplate->town = substr($objAddress->city, 0, 30);
        $objTemplate->region = substr($objAddress->subdivision, 0, 30);
        $objTemplate->postcode = substr($objAddress->postal, 0, 12);
        $objTemplate->country = strtoupper($objAddress->country);
        $objTemplate->tel = substr($objAddress->phone, 0, 30);
        $objTemplate->email = substr($objAddress->email, 0, 80);

        $objTemplate->id = $this->id;
        $objTemplate->debug = $this->debug;
        $objTemplate->action = ($this->debug ? 'https://secure-test.worldpay.com/wcc/purchase' : '');

        return $objTemplate->parse();
    }
}
