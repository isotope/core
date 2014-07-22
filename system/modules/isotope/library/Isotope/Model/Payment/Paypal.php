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

use Haste\Http\Response\Response;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Product;
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
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        $objRequest = new \Request();
        $objRequest->send(('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate'), file_get_contents("php://input"), 'post');

        if ($objRequest->hasError()) {
            \System::log('Request Error: ' . $objRequest->error, __METHOD__, TL_ERROR);
            exit;
        } elseif ($objRequest->response == 'VERIFIED' && (\Input::post('receiver_email', true) == $this->paypal_account || $this->debug)) {
            // Validate payment data (see #2221)
            if ($objOrder->currency != \Input::post('mc_currency') || $objOrder->getTotal() != \Input::post('mc_gross')) {
                \System::log('IPN manipulation in payment from "' . \Input::post('payer_email') . '" !', __METHOD__, TL_ERROR);

                return;
            }

            if (!$objOrder->checkout()) {
                \System::log('IPN checkout for Order ID "' . \Input::post('invoice') . '" failed', __METHOD__, TL_ERROR);

                return;
            }

            // Store request data in order for future references
            $arrPayment = deserialize($objOrder->payment_data, true);
            $arrPayment['POSTSALE'][] = $_POST;
            $objOrder->payment_data = $arrPayment;
            $objOrder->save();

            // @see https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/ipnguide.pdf
            switch (\Input::post('payment_status')) {
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
        } else {
            \System::log('PayPal IPN: data rejected (' . $objRequest->response . ')', __METHOD__, TL_ERROR);
        }

        // 200 OK
        $objResponse = new Response();
        $objResponse->send();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('invoice'));
    }

    /**
     * Return the PayPal form.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $arrData     = array();
        $fltDiscount = 0;
        $i           = 0;

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

            $arrData['item_number_' . ++$i] = $objItem->getSku();
            $arrData['item_name_' . $i]     = $objItem->getName() . $strConfig;
            $arrData['amount_' . $i]        = $objItem->getPrice();
            $arrData['quantity_' . $i]      = $objItem->quantity;
        }

        foreach ($objOrder->getSurcharges() as $objSurcharge) {

            if (!$objSurcharge->addToTotal) {
                continue;
            }

            // PayPal does only support one single discount item
            if ($objSurcharge->total_price < 0) {
                $fltDiscount -= $objSurcharge->total_price;
                continue;
            }

            $arrData['item_name_' . ++$i] = $objSurcharge->label;
            $arrData['amount_' . $i]      = $objSurcharge->total_price;
        }

        $objTemplate = new \Isotope\Template('iso_payment_paypal');
        $objTemplate->setData($this->arrData);

        $objTemplate->id            = $this->id;
        $objTemplate->action        = ('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr');
        $objTemplate->invoice       = $objOrder->id;
        $objTemplate->data          = array_map('specialchars', $arrData);
        $objTemplate->discount      = $fltDiscount;
        $objTemplate->address       = $objOrder->getBillingAddress();
        $objTemplate->currency      = $objOrder->currency;
        $objTemplate->return        = \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder);
        $objTemplate->cancel_return = \Environment::get('base') . $objModule->generateUrlForStep('failed');
        $objTemplate->notify_url    = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->headline      = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message       = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->slabel        = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);

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
        if (($objOrder = Order::findByPk($orderId)) === null) {
            return parent::backendInterface($orderId);
        }

        $arrPayment = deserialize($objOrder->payment_data, true);

        if (!is_array($arrPayment['POSTSALE']) || empty($arrPayment['POSTSALE'])) {
            return parent::backendInterface($orderId);
        }

        $arrPayment = array_pop($arrPayment['POSTSALE']);
        ksort($arrPayment);
        $i = 0;

        $strBuffer = '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=payment', '', \Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.paypal'][0] . ')' . '</h2>

<div id="tl_soverview">
<div id="tl_messages">
<p class="tl_info"><a href="https://www.paypal.com/' . strtolower($arrPayment['residence_country']) . '/cgi-bin/webscr?cmd=_view-a-trans&id=' . $arrPayment['txn_id'] . '" target="_blank">' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionOnline'] . '</a></p>
</div>
</div>

<table class="tl_show">
  <tbody>';

        foreach ($arrPayment as $k => $v) {
            if (is_array($v))
                continue;

            $strBuffer .= '
  <tr>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

            ++$i;
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
    }
}
