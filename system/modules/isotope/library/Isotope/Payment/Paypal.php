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
 * Class Paypal
 *
 * Handle Paypal payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Paypal extends Payment implements IsotopePayment
{

    /**
     * processPayment function.
     *
     * @access public
     * @return void
     */
    public function processPayment()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', $this->Isotope->Cart->id)) === null)
        {
            return false;
        }

        if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
        {
            \Isotope\Frontend::clearTimeout();

            return true;
        }

        if (\Isotope\Frontend::setTimeout())
        {
            // Do not index or cache the page
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate = new \Isotope\Template('mod_message');
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
        $objRequest = new Request();
        $objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate'), http_build_query($_POST), 'post');

        if ($objRequest->hasError())
        {
            $this->log('Request Error: ' . $objRequest->error, __METHOD__, TL_ERROR);
            exit;
        }
        elseif ($objRequest->response == 'VERIFIED' && (\Input::post('receiver_email', true) == $this->paypal_account || $this->debug))
        {
            if (($objOrder = Order::findByPk(\Input::post('invoice'))) === null)
            {
                $this->log('Order ID "' . \Input::post('invoice') . '" not found', __METHOD__, TL_ERROR);

                return;
            }

            // Validate payment data (see #2221)
            if ($objOrder->currency != \Input::post('mc_currency') || $objOrder->grandTotal != \Input::post('mc_gross'))
            {
                $this->log('IPN manipulation in payment from "' . \Input::post('payer_email') . '" !', __METHOD__, TL_ERROR);

                return;
            }

            if (!$objOrder->checkout())
            {
                $this->log('IPN checkout for Order ID "' . \Input::post('invoice') . '" failed', __METHOD__, TL_ERROR);

                return;
            }

            // Load / initialize data
            $arrPayment = deserialize($objOrder->payment_data, true);

            // Store request data in order for future references
            $arrPayment['POSTSALE'][] = $_POST;


            $arrData = $objOrder->getData();
            $arrData['old_payment_status'] = $arrPayment['status'];

            $arrPayment['status'] = \Input::post('payment_status');
            $arrData['new_payment_status'] = $arrPayment['status'];

            // array('pending','processing','complete','on_hold', 'cancelled'),
            switch( $arrPayment['status'] )
            {
                case 'Completed':
                    $objOrder->date_paid = time();
                    $objOrder->updateOrderStatus($this->new_order_status);
                    break;

                case 'Canceled_Reversal':
                case 'Denied':
                case 'Expired':
                case 'Failed':
                case 'Voided':
                    $objOrder->date_paid = '';
                    $objOrder->updateOrderStatus($this->Isotope->Config->orderstatus_error);
                    break;

                case 'In-Progress':
                case 'Partially_Refunded':
                case 'Pending':
                case 'Processed':
                case 'Refunded':
                case 'Reversed':
                    break;
            }

            $objOrder->payment_data = $arrPayment;

            $objOrder->save();

            $this->log('PayPal IPN: data accepted', __METHOD__, TL_GENERAL);
        }
        else
        {
            $this->log('PayPal IPN: data rejected (' . $objRequest->response . ')', __METHOD__, TL_ERROR);
        }

        header('HTTP/1.1 200 OK');
        exit;
    }


    /**
     * Return the PayPal form.
     *
     * @access public
     * @return string
     */
    public function checkoutForm()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', $this->Isotope->Cart->id)) === null) {
            $this->redirect($this->addToUrl('step=failed', true));
        }

        $arrData = array();
        $fltDiscount = 0;

        foreach ($this->Isotope->Cart->getProducts() as $objProduct) {

            $strOptions = '';
            $arrOptions = $objProduct->getOptions();

            if (is_array($arrOptions) && !empty($arrOptions)) {
                $options = array();

                foreach( $arrOptions as $option ) {
                    $options[] = $option['label'] . ': ' . $option['value'];
                }

                $strOptions = ' ('.implode(', ', $options).')';
            }

            $arrData['item_number_'.++$i] = $objProduct->sku;
            $arrData['item_name_'.$i] = $objProduct->name . $strOptions;
            $arrData['amount_'.$i] = $objProduct->price;
            $arrData['quantity_'.$i] = $objProduct->quantity_requested;
        }


        foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge ) {

            if ($arrSurcharge['add'] === false) {
                continue;
            }

            // PayPal does only support one single discount item
            if ($arrSurcharge['total_price'] < 0) {
                $fltDiscount -= $arrSurcharge['total_price'];
                continue;
            }

            $arrData['item_name_'.++$i] = $arrSurcharge['label'];
            $arrData['amount_'.$i] = $arrSurcharge['total_price'];
        }


        $objTemplate = new \Isotope\Template('iso_payment_datatrans');
        $objTemplate->setData($this->arrData);

        $objTemplate->id = $this->id;
        $objTemplate->action = ('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr');
        $objTemplate->invoice = $objOrder->id;
        $objTemplate->data = $arrData;
        $objTemplate->discount = $fltDiscount;
        $objTemplate->address = $this->Isotope->Cart->billingAddress;
        $objTemplate->currency = $this->Isotope->Config->currency;
        $objTemplate->return = \Environment::get('base') . \Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->addToUrl('step=complete', true));
        $objTemplate->cancel_return = \Environment::get('base') . $this->addToUrl('step=failed', true);
        $objTemplate->notify_url = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

        return $objTemplate->parse();
    }

    /**
     * Return information or advanced features in the backend.
     *
     * Use this function to present advanced features or basic payment information for an order in the backend.
     * @param integer Order ID
     * @return string
     */
    public function backendInterface($orderId)
    {
        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('id', $orderId))
        {
            return parent::backendInterface($orderId);
        }

        $arrPayment = $objOrder->payment_data;

        if (!is_array($arrPayment['POSTSALE']) || empty($arrPayment['POSTSALE']))
        {
            return parent::backendInterface($orderId);
        }

        $arrPayment = array_pop($arrPayment['POSTSALE']);
        ksort($arrPayment);
        $i = 0;

        $strBuffer = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=payment', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['PAY'][$this->type][0] . ')' . '</h2>

<div id="tl_soverview">
<div id="tl_messages">
<p class="tl_info"><a href="https://www.paypal.com/' . strtolower($arrPayment['residence_country']) . '/cgi-bin/webscr?cmd=_view-a-trans&id=' . $arrPayment['txn_id'] . '" target="_blank">' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionOnline'] . '</a></p>
</div>
</div>

<table class="tl_show">
  <tbody>';

        foreach ($arrPayment as $k => $v)
        {
            if (is_array($v))
                continue;

            $strBuffer .= '
  <tr>
    <td' . ($i%2 ? '' : ' class="tl_bg"') . '><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i%2 ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

            ++$i;
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
    }
}
