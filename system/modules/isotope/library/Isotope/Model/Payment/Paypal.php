<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Contao\Environment;
use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Isotope\Haste\Util\StringUtil as HasteStringUtil;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * PayPal Standard payment method
 *
 * @property string $paypal_account
 *
 * @see https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/ipnguide.pdf
 */
class Paypal extends Postsale
{

    /**
     * Process PayPal Instant Payment Notifications (IPN)
     *
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objOrder->getId() . '" is not purchasable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if ('Completed' !== Input::post('payment_status')) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, 'PayPal IPN: payment status "' . Input::post('payment_status') . '" not implemented', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::GENERAL)]);
            return new Response();
        }

        if (!$this->validateInput()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->debug && 0 !== strcasecmp(Input::post('receiver_email', true), $this->paypal_account)) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'PayPal IPN: Account email does not match (got ' . Input::post('receiver_email', true) . ', expected ' . $this->paypal_account . ')', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        // Validate payment data (see #2221)
        if ($objOrder->getCurrency() !== Input::post('mc_currency')
            || $objOrder->getTotal() != Input::post('mc_gross')
        ) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'PayPal IPN: manipulation in payment from "' . Input::post('payer_email') . '" !', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if ($objOrder->isCheckoutComplete()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, 'PayPal IPN: checkout for Order ID "' . Input::post('invoice') . '" already completed', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::GENERAL)]);
            return new Response();
        }

        if (!$objOrder->checkout()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'PayPal IPN: checkout for Order ID "' . Input::post('invoice') . '" failed', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Store request data in order for future references
        $arrPayment = StringUtil::deserialize($objOrder->payment_data, true);
        $arrPayment['POSTSALE'][] = $_POST;
        $objOrder->payment_data = $arrPayment;

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, 'PayPal IPN: data accepted', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::GENERAL)]);

        return new Response();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) Input::post('invoice'));
    }

    /**
     * Return the PayPal form.
     *
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objOrder->getId() . '" is not purchasable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return false;
        }

        $arrData     = array();
        $fltDiscount = 0;
        $i           = 0;

        foreach ($objOrder->getItems() as $objItem) {

            // Set the active product for insert tags replacement
            if ($objItem->hasProduct()) {
                Product::setActive($objItem->getProduct());
            }

            $strConfig = '';
            $arrConfig = $objItem->getConfiguration();

            if (!empty($arrConfig)) {

                array_walk(
                    $arrConfig,
                    function(&$option) {
                        $option = $option['label'] . ': ' . $option;
                    }
                );

                $strConfig = ' (' . implode(', ', $arrConfig) . ')';
            }

            $strName = HasteStringUtil::convertToText(
                $objItem->getName() . $strConfig,
                HasteStringUtil::NO_TAGS | HasteStringUtil::NO_BREAKS | HasteStringUtil::NO_INSERTTAGS | HasteStringUtil::NO_ENTITIES
            );

            // Make sure name is not empty, otherwise PayPal ignores all subsequent products
            // @see https://github.com/isotope/core/issues/2176
            if (empty($strName)) {
                $strName = 'ID '.$objItem->id;
            }

            $arrData['item_number_' . ++$i] = $objItem->getSku();
            $arrData['item_name_' . $i]     = $strName;
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

            $arrData['item_name_' . ++$i] = HasteStringUtil::convertToText(
                $objSurcharge->label,
                HasteStringUtil::NO_TAGS | HasteStringUtil::NO_BREAKS | HasteStringUtil::NO_INSERTTAGS | HasteStringUtil::NO_ENTITIES
            );
            $arrData['amount_' . $i]      = $objSurcharge->total_price;
        }

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_paypal');
        $objTemplate->setData($this->arrData);

        $objTemplate->id            = $this->id;
        $objTemplate->action        = ('https://www.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr');
        $objTemplate->invoice       = $objOrder->getId();
        $objTemplate->data          = array_map([StringUtil::class, 'specialchars'], $arrData);
        $objTemplate->discount      = $fltDiscount;
        $objTemplate->address       = $objOrder->getBillingAddress();
        $objTemplate->currency      = $objOrder->getCurrency();
        $objTemplate->return        = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true);
        $objTemplate->cancel_return = Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true);
        $objTemplate->notify_url    = System::getContainer()->get('router')->generate('isotope_postsale', ['mod' => 'pay', 'id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);
        $objTemplate->headline      = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message       = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel        = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

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

        $arrPayment = StringUtil::deserialize($objOrder->payment_data, true);

        if (!\is_array($arrPayment['POSTSALE']) || empty($arrPayment['POSTSALE'])) {
            return parent::backendInterface($orderId);
        }

        $arrPayment = array_pop($arrPayment['POSTSALE']);
        ksort($arrPayment);
        $i = 0;

        $strBuffer = '
<div id="tl_buttons">
<a href="' . \Contao\StringUtil::ampersand(str_replace('&key=payment', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment']['paypal'][0] . ')' . '</h2>

<div id="tl_soverview">
<div id="tl_messages">
<p class="tl_info"><a href="https://www.paypal.com/' . strtolower($arrPayment['residence_country']) . '/cgi-bin/webscr?cmd=_view-a-trans&id=' . $arrPayment['txn_id'] . '" target="_blank">' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionOnline'] . '</a></p>
</div>
</div>

<table class="tl_show">
  <tbody>';

        foreach ($arrPayment as $k => $v) {
            if (\is_array($v)) {
                continue;
            }

            $strBuffer .= '
  <tr>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

            ++$i;
        }

        return $strBuffer . '
</tbody></table>
</div>';
    }

    /**
     * Validate PayPal request data by sending it back to the PayPal servers.
     *
     * @return bool
     */
    private function validateInput()
    {
        $client = HttpClient::create();

        try {
            $body = file_get_contents('php://input');
            $url = 'https://ipnpb.' . ($this->debug ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate';

            $response = $client->request('POST', $url, [
                'body' => $body
            ]);

            if ('VERIFIED' !== $response->getContent()) {
                \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'PayPal IPN: data rejected (' . $response->getContent() . ')', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

                return false;
            }
        } catch (ExceptionInterface $exception) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'PayPal IPN: Request Error (' . $exception->getMessage() . ')', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return false;
        }

        return true;
    }
}
