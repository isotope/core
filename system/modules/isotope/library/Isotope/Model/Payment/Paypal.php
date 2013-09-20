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


/**
 * Class Paypal
 *
 * Handle Paypal payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Paypal extends Postsale implements IsotopePayment
{

    /**
     * Process PayPal Instant Payment Notifications (IPN)
     *
     * @access public
     * @return void
     */
    public function processPostsale()
    {
        $objRequest = new \Request();
        $objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate'), file_get_contents("php://input"), 'post');

        if ($objRequest->hasError())
        {
            \System::log('Request Error: ' . $objRequest->error, __METHOD__, TL_ERROR);
            exit;
        }
        elseif ($objRequest->response == 'VERIFIED' && (\Input::post('receiver_email', true) == $this->paypal_account || $this->debug))
        {
            if (($objOrder = Order::findByPk(\Input::post('invoice'))) === null)
            {
                \System::log('Order ID "' . \Input::post('invoice') . '" not found', __METHOD__, TL_ERROR);

                return;
            }

            // Validate payment data (see #2221)
            if ($objOrder->currency != \Input::post('mc_currency') || $objOrder->getTotal() != \Input::post('mc_gross'))
            {
                \System::log('IPN manipulation in payment from "' . \Input::post('payer_email') . '" !', __METHOD__, TL_ERROR);

                return;
            }

            if (!$objOrder->checkout())
            {
                \System::log('IPN checkout for Order ID "' . \Input::post('invoice') . '" failed', __METHOD__, TL_ERROR);

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
                    // PayPal will also send this notification if the order has not been placed.
                    // What do we do here?
//                    $objOrder->date_paid = '';
//                    $objOrder->updateOrderStatus(Isotope::getConfig()->orderstatus_error);
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

            \System::log('PayPal IPN: data accepted', __METHOD__, TL_GENERAL);
        }
        else
        {
            \System::log('PayPal IPN: data rejected (' . $objRequest->response . ')', __METHOD__, TL_ERROR);
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
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null) {
            \Isotope\Module\Checkout::redirectToStep('failed');
        }

        $arrData = array();
        $fltDiscount = 0;

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

            $arrData['item_number_'.++$i]   = $objItem->getSku();
            $arrData['item_name_'.$i]       = $objItem->getName() . $strOptions;
            $arrData['amount_'.$i]          = $objItem->getPrice();
            $arrData['quantity_'.$i]        = $objItem->quantity;
        }

        foreach (Isotope::getCart()->getSurcharges() as $objSurcharge) {

            if (!$objSurcharge->add) {
                continue;
            }

            // PayPal does only support one single discount item
            if ($objSurcharge->total_price < 0) {
                $fltDiscount -= $objSurcharge->total_price;
                continue;
            }

            $arrData['item_name_'.++$i] = $objSurcharge->getLabel();
            $arrData['amount_'.$i] = $objSurcharge->total_price;
        }

        $objTemplate = new \Isotope\Template('iso_payment_paypal');
        $objTemplate->setData($this->arrData);

        $objTemplate->id = $this->id;
        $objTemplate->action = ('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr');
        $objTemplate->invoice = $objOrder->id;
        $objTemplate->data = $arrData;
        $objTemplate->discount = $fltDiscount;
        $objTemplate->address = Isotope::getCart()->getBillingAddress();
        $objTemplate->currency = Isotope::getConfig()->currency;
        $objTemplate->return = \Environment::get('base') . \Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, \Isotope\Module\Checkout::generateUrlForStep('complete'));
        $objTemplate->cancel_return = \Environment::get('base') . \Isotope\Module\Checkout::generateUrlForStep('failed');
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
