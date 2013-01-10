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

        if (($objOrder = Order::findOneBy('cart_id', $this->Isotope->Cart->id)) === null)
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
            'successurl'		=> $this->Environment->base . $this->addToUrl('step=complete', true) . '?uid=' . $objOrder->uniqid,
            'backurl'			=> $this->Environment->base . $this->addToUrl('step=failed', true),
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

        $strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>
<form id="payment_form" action="https://secure.pay1.de/frontend/" method="post">';

        foreach( $arrData as $k => $v )
        {
            $strBuffer .= "\n" . '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
        }

        $strBuffer .= '
<input type="hidden" name="hash" value="' . $strHash . '" />

<input type="hidden" name="company" value="' . $this->Isotope->Cart->billing_address['company'] . '">
<input type="hidden" name="firstname" value="' . $this->Isotope->Cart->billing_address['firstname'] . '">
<input type="hidden" name="lastname" value="' . $this->Isotope->Cart->billing_address['lastname'] . '">
<input type="hidden" name="street" value="' . $this->Isotope->Cart->billing_address['street_1'] . '">
<input type="hidden" name="zip" value="' . $this->Isotope->Cart->billing_address['postal'] . '">
<input type="hidden" name="city" value="' . $this->Isotope->Cart->billing_address['city'] . '">
<input type="hidden" name="country" value="' . strtoupper($this->Isotope->Cart->billing_address['country']) . '">
<input type="hidden" name="email" value="' . $this->Isotope->Cart->billing_address['email'] . '">
<input type="hidden" name="telephonenumber" value="' . $this->Isotope->Cart->billing_address['phone'] . '">
<input type="hidden" name="language" value="' . strtoupper($GLOBALS['TL_LANGUAGE']) . '" />

<input type="submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]) . '">
</form>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent( \'domready\' , function() {
  $(\'payment_form\').submit();
});
//--><!]]>
</script>';

        return $strBuffer;
    }


    /**
     * Return a list of valid credit card types for this payment module
     */
    public function getAllowedCCTypes()
    {
        return array();
    }
}
