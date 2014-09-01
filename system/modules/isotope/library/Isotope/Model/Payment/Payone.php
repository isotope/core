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
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;


class Payone extends Postsale implements IsotopePayment
{

    /**
     * Process Transaction URL notification
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (\Input::post('aid') != $this->payone_aid
            || \Input::post('portalid') != $this->payone_portalid
            || (\Input::post('mode') == 'test' && !$this->debug)
            || (\Input::post('mode') == 'live' && $this->debug)
        ) {
            \System::log('PayOne configuration mismatch', __METHOD__, TL_ERROR);
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
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('reference'));
    }

    /**
     * HTML form for checkout
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $i = 0;

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
            'successurl'        => \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder),
            'backurl'           => \Environment::get('base') . $objModule->generateUrlForStep('failed'),
            'amount'            => ($objOrder->getTotal() * 100),
            'currency'          => $objOrder->currency,

            // Custom parameter to recognize payone in postsale request (only alphanumeric is allowed)
            'param'             => 'paymentMethodPayone' . $this->id
        );

        foreach ($objOrder->getItems() as $objItem) {

            $strConfig = '';
            $arrConfig = $objItem->getConfiguration();

            if (!empty($arrConfig)) {

                array_walk(
                    $arrConfig,
                    function(&$option) {
                        $option = $option['label'] . ': ' . (string) $option;
                    }
                );

                $strConfig = ' (' . implode(', ', $arrConfig) . ')';
            }

            $arrData['id[' . ++$i . ']'] = $objItem->getSku();
            $arrData['pr[' . $i . ']']   = round($objItem->getPrice(), 2) * 100;
            $arrData['no[' . $i . ']']   = $objItem->quantity;
            $arrData['de[' . $i . ']']   = specialchars($objItem->getName() . $strConfig);
        }

        foreach ($objOrder->getSurcharges() as $k => $objSurcharge) {

            if (!$objSurcharge->addToTotal)
                continue;

            $arrData['id[' . ++$i . ']'] = 'surcharge' . $k;
            $arrData['pr[' . $i . ']']   = $objSurcharge->total_price * 100;
            $arrData['no[' . $i . ']']   = '1';
            $arrData['de[' . $i . ']']   = $objSurcharge->label;
        }


        ksort($arrData);
        // Do not urlencode values because Payone does not properly decode POST values (whatever...)
        $strHash = md5(implode('', $arrData) . $this->payone_key);

        $objTemplate                  = new \Isotope\Template('iso_payment_payone');
        $objTemplate->id              = $this->id;
        $objTemplate->data            = $arrData;
        $objTemplate->hash            = $strHash;
        $objTemplate->billing_address = $objOrder->getBillingAddress()->row();
        $objTemplate->headline        = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message         = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel          = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

        return $objTemplate->parse();
    }
}
