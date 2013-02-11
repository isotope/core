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

namespace Isotope\Payment;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Product\Collection\Order;


/**
 * Class CybersourceClient
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Payone extends Payment implements IsotopePayment
{

    /**
     * Process checkout payment.
     *
     * @access public
     * @return mixed
     */
    public function processPayment()
    {
        return true;
    }


    /**
     * Process Transaction URL notification
     *
     * @access public
     * @return void
     */
    public function processPostSale()
    {
        if (\Input::post('aid') == $this->payone_aid
            && \Input::post('portalid') == $this->payone_portalid
            && ((\Input::post('mode') == 'test' && $this->debug) || (\Input::post('mode') == 'live' && !$this->debug)))
        {
            if (($objOrder = Order::findByPk(\Input::post('reference'))) !== null)
            {
                if (\Input::post('txaction') == 'paid'
                    && \Input::post('currency') == $objOrder->currency
                    && \Input::post('balance') <= 0)
                {
                    $objOrder->date_payed = time();

                    if (ISO_VERSION > 0.2)
                    {
                        $objOrder->checkout();
                    }

                    $objOrder->save();
                }
            }
        }

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

        if (($objOrder = Order::findOneBy('source_collection_id', $this->Isotope->Cart->id)) === null)
        {
            $this->redirect($this->addToUrl('step=failed', true));
        }

        $arrData = array
        (
            'aid'				=> $this->payone_aid,
            'portalid'			=> $this->payone_portalid,
            'mode'				=> ($this->debug ? 'test' : 'live'),
            'request'			=> ($this->trans_type=='auth' ? 'preauthorization' : 'authorization'),
            'encoding'			=> 'UTF-8',
            'clearingtype'		=> $this->payone_clearingtype,
            'reference'			=> $objOrder->id,
            'display_name'		=> 'no',
            'display_address'	=> 'no',
            'successurl'		=> \Environment::get('base') . $this->addToUrl('step=complete', true) . '?uid=' . $objOrder->uniqid,
            'backurl'			=> \Environment::get('base') . $this->addToUrl('step=failed', true),
            'amount'			=> ($this->Isotope->Cart->grandTotal * 100),
            'currency'			=> $this->Isotope->Config->currency,
        );

        foreach( $this->Isotope->Cart->getProducts() as $objProduct )
        {
            $strOptions = '';
            $arrOptions = $objProduct->getOptions();

            if (is_array($arrOptions) && !empty($arrOptions))
            {
                $options = array();

                foreach( $arrOptions as $option )
                {
                    $options[] = $option['label'] . ': ' . $option['value'];
                }

                $strOptions = ' ('.implode(', ', $options).')';
            }

            $arrData['id['.++$i.']']	= $objProduct->sku;
            $arrData['pr['.$i.']']		= round($objProduct->price, 2) * 100;
            $arrData['no['.$i.']']		= $objProduct->quantity_requested;
            $arrData['de['.$i.']']		= specialchars($objProduct->name . $strOptions);
        }

        foreach( $this->Isotope->Cart->getSurcharges() as $k => $arrSurcharge )
        {
            if ($arrSurcharge['add'] === false)
                continue;

            $arrData['id['.++$i.']']	= 'surcharge'.$k;
            $arrData['pr['.$i.']']		= $arrSurcharge['total_price'] * 100;
            $arrData['no['.$i.']']		= '1';
            $arrData['de['.$i.']']		= $arrSurcharge['label'];
        }


        ksort($arrData);
        $arrData = array_map('urlencode', $arrData);
        $strHash = md5(implode('', $arrData) . $this->payone_key);

        $objTemplate = new \Isotope\Template('iso_payment_payone');
        $objTemplate->id = $this->id;
        $objTemplate->data = $arrData;
        $objTemplate->hash = $strHasn;
        $objTemplate->billing_address = $this->Isotope->Cart->billing_address;
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

        return $objTemplate->parse();
    }


    /**
     * Return a list of valid credit card types for this payment module
     */
    public function getAllowedCCTypes()
    {
        return array();
    }
}
