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

namespace Isotope\Model\Payment;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Model\ProductCollection\Order;


class Payone extends Postsale implements IsotopePayment
{

    /**
     * Process Transaction URL notification
     *
     * @access public
     * @return void
     */
    public function processPostsale()
    {
        if (\Input::post('aid') != $this->payone_aid
            || \Input::post('portalid') != $this->payone_portalid
            || (\Input::post('mode') == 'test' && !$this->debug)
            || (\Input::post('mode') == 'live' && $this->debug)
        ) {
            \System::log('PayOne configuration mismatch', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        if (($objOrder = Order::findByPk(\Input::post('reference'))) === null) {
            \System::log('Order ID "'.\Input::post('reference').'" not found', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        if (\Input::post('txaction') != 'paid'
            && \Input::post('currency') != $objOrder->currency
            && \Input::post('balance') > 0
        ) {
            \System::log('PayOne order data mismatch for Order ID "' . \Input::post('invoice') . '"', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('invoice') . '" failed', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        // PayOne must get TSOK as return value, otherwise the request will be sent again
        die('TSOK');
    }


    /**
     * HTML form for checkout
     *
     * @access public
     * @return mixed
     */
    public function checkoutForm()
    {
        $i = 0;

        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null)
        {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $arrData = array
        (
            'aid'               => $this->payone_aid,
            'portalid'          => $this->payone_portalid,
            'mode'              => ($this->debug ? 'test' : 'live'),
            'request'           => ($this->trans_type=='auth' ? 'preauthorization' : 'authorization'),
            'encoding'          => 'UTF-8',
            'clearingtype'      => $this->payone_clearingtype,
            'reference'         => $objOrder->id,
            'display_name'      => 'no',
            'display_address'   => 'no',
            'successurl'        => \Environment::get('base') . \Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, \Isotope\Module\Checkout::generateUrlForStep('complete')),
            'backurl'           => \Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed'),
            'amount'            => (Isotope::getCart()->getTotal() * 100),
            'currency'          => Isotope::getConfig()->currency,
        );

        foreach (Isotope::getCart()->getItems() as $objItem) {

            // Set the active product for insert tags replacement
            $GLOBALS['ACTIVE_PRODUCT'] = $objItem->getProduct();

            $strOptions = '';
            $arrOptions = Isotope::formatOptions($objItem->getOptions());

            unset($GLOBALS['ACTIVE_PRODUCT']);

            if (!empty($arrOptions)) {

                array_walk(
                    $arrOptions,
                    function($option) {
                        return $option['label'] . ': ' . $option['value'];
                    }
                );

                $strOptions = ' (' . implode(', ', $arrOptions) . ')';
            }

            $arrData['id['.++$i.']']    = $objItem->getSku();
            $arrData['pr['.$i.']']      = round($objItem->getPrice(), 2) * 100;
            $arrData['no['.$i.']']      = $objItem->quantity;
            $arrData['de['.$i.']']      = specialchars($objItem->getName() . $strOptions);
        }

        foreach (Isotope::getCart()->getSurcharges() as $k => $objSurcharge) {

            if (!$objSurcharge->add)
                continue;

            $arrData['id['.++$i.']']    = 'surcharge'.$k;
            $arrData['pr['.$i.']']      = $objSurcharge->total_price * 100;
            $arrData['no['.$i.']']      = '1';
            $arrData['de['.$i.']']      = $objSurcharge->getLabel();
        }


        ksort($arrData);
        $arrData = array_map('urlencode', $arrData);
        $strHash = md5(implode('', $arrData) . $this->payone_key);

        $objTemplate = new \Isotope\Template('iso_payment_payone');
        $objTemplate->id = $this->id;
        $objTemplate->data = $arrData;
        $objTemplate->hash = $strHash;
        $objTemplate->billing_address = Isotope::getCart()->getBillingAddress()->row();
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

        return $objTemplate->parse();
    }
}
