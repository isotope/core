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
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;


/**
 * Class PSP
 *
 * Handle PSP payments
 *
 * @property string $psp_pspid
 * @property string $psp_http_method
 * @property string $psp_hash_method
 * @property string $psp_hash_in
 * @property string $psp_hash_out
 * @property string $psp_dynamic_template
 * @property string $psp_payment_method
 */
abstract class PSP extends Payment implements IsotopePostsale
{
    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        // If the order has already been placed through postsale
        if ($objOrder->isCheckoutComplete()) {
            return true;
        }

        // In processPayment, the parameters are always in GET
        $this->psp_http_method = 'GET';

        return $this->processPostsale($objOrder);
    }

    /**
     * Process post-sale request from the PSP payment server.
     *
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        if (!$this->validateSHASign()) {
            System::log('Received invalid postsale data for order ID "' . $objOrder->getId() . '"', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment data
        if ($objOrder->getCurrency() !== $this->getRequestData('currency')
            || $objOrder->getTotal() != $this->getRequestData('amount')
        ) {
            System::log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->getId() . '!', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment status
        switch ($this->getRequestData('STATUS')) {
            case 9:  // Zahlung beantragt (Authorize & Capture)
                $objOrder->setDatePaid(time());
                // no break

            case 5:  // Genehmigt (Authorize ohne Capture)
                $intStatus = $this->new_order_status;
                break;

            case 41: // Unbekannter Wartezustand
            case 51: // Genehmigung im Wartezustand
            case 91: // Zahlung im Wartezustand
            case 52: // Genehmigung nicht bekannt
            case 92: // Zahlung unsicher

                /** @var \Isotope\Model\Config $objConfig */
                if (($objConfig = $objOrder->getConfig()) === null) {
                    System::log('Config for Order ID ' . $objOrder->getId() . ' not found', __METHOD__, TL_ERROR);
                    return false;
                }

                $intStatus = $objConfig->orderstatus_error;
                break;

            case 0:  // Ungültig / Unvollständig
            case 1:  // Zahlungsvorgang abgebrochen
            case 2:  // Genehmigung verweigert
            case 4:  // Gespeichert
            case 93: // Bezahlung verweigert
            default:
                return false;
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return true;
        }

        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);
            return false;
        }

        $objOrder->payment_data = json_encode($this->getRawRequestData());

        $objOrder->updateOrderStatus($intStatus);
        $objOrder->save();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        if (!$this->getRequestData('orderID')) {
            return null;
        }

        return Order::findByPk((int) $this->getRequestData('orderID'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $arrParams = $this->preparePSPParams($objOrder, $objModule);

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        $strSHASign = '';
        foreach ($arrParams as $k => $v) {
            if ($v == '') {
                continue;
            }

            $strSHASign .= $k . '=' . htmlspecialchars_decode($v) . $this->psp_hash_in;
        }

        $arrParams['SHASIGN'] = strtoupper(hash($this->psp_hash_method, $strSHASign));

        /** @var Template|object $objTemplate */
        $objTemplate = new Template($this->strTemplate);
        $objTemplate->setData($this->arrData);

        $objTemplate->params   = $arrParams;
        $objTemplate->headline = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * Gets the available payment methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return array(
            'CreditCard__American_Express'          => 'CreditCard - American Express',
            'CreditCard__Billy'                     => 'CreditCard - Billy',
            'CreditCard__CB'                        => 'CreditCard - CB',
            'CreditCard__Diners_Club'               => 'CreditCard - Diners Club',
            'CreditCard__JCB'                       => 'CreditCard - JCB',
            'CreditCard__MaestroUK'                 => 'CreditCard - MaestroUK',
            'CreditCard__MasterCard'                => 'CreditCard - MasterCard',
            'CreditCard__VISA'                      => 'CreditCard - VISA',
            'PostFinance_Card__PostFinance_Card'    => 'PostFinance Card',
            'DirectEbanking__Sofort_Uberweisung'    => 'Sofortüberweisung (Deutsche Konten)',
            'DirectEbankingAT__DirectEbankingAT'    => 'Sofortüberweisung (AT)',
            'DirectEbankingCH__DirectEbankingCH'    => 'Sofortüberweisung (CH)',
            'DirectEbankingDE__DirectEbankingDE'    => 'Sofortüberweisung (DE)',
            'DirectEbankingBE__DirectEbankingBE'    => 'DirectEbanking (BE)',
            'DirectEbankingFR__DirectEbankingFR'    => 'DirectEbanking (FR)',
            'DirectEbankingGB__DirectEbankingGB'    => 'DirectEbanking (GB)',
            'DirectEbankingIT__DirectEbankingIT'    => 'DirectEbanking (IT)',
            'DirectEbankingNL__DirectEbankingNL'    => 'DirectEbanking (NL)',
            'EPS__EPS'                              => 'EPS',
            'PAYPAL__PAYPAL'                        => 'PayPal'
        );
    }

    /**
     * Prepare PSP params
     *
     * @param IsotopePurchasableCollection $objOrder
     * @param Module $objModule
     *
     * @return  array
     */
    protected function preparePSPParams(IsotopePurchasableCollection $objOrder, $objModule)
    {
        $objBillingAddress = $objOrder->getBillingAddress();

        $arrParams = array
        (
            'PSPID'         => $this->psp_pspid,
            'ORDERID'       => $objOrder->getId(),
            'AMOUNT'        => round($objOrder->getTotal() * 100),
            'CURRENCY'      => $objOrder->getCurrency(),
            'LANGUAGE'      => $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
            'CN'            => html_entity_decode($objBillingAddress->firstname . ' ' . $objBillingAddress->lastname),
            'EMAIL'         => html_entity_decode($objBillingAddress->email),
            'OWNERZIP'      => html_entity_decode($objBillingAddress->postal),
            'OWNERADDRESS'  => substr(html_entity_decode($objBillingAddress->street_1), 0, 35),
            'OWNERADDRESS2' => substr(html_entity_decode($objBillingAddress->street_2), 0, 35),
            'OWNERCTY'      => strtoupper($objBillingAddress->country),
            'OWNERTOWN'     => substr(html_entity_decode($objBillingAddress->city), 0, 35),
            'OWNERTELNO'    => preg_replace('/[^- +\/0-9]/', '', $objBillingAddress->phone),
            'ACCEPTURL'     => Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true),
            'DECLINEURL'    => Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true),
            'BACKURL'       => Checkout::generateUrlForStep(Checkout::STEP_REVIEW, null, null, true),
            'PARAMPLUS'     => 'mod=pay&amp;id=' . $this->id,
            'TP'            => $this->psp_dynamic_template ? : ''
        );

        // Add PostFinance specific PSP payment methods
        if ($this->psp_payment_method) {
            $chunks = explode('__', $this->psp_payment_method, 2);
            $arrParams = array_merge(
                $arrParams,
                array(
                    'PM'    => str_replace('_', ' ', $chunks[0]),
                    'BRAND' => str_replace('_', ' ', $chunks[1]),
                )
            );
        }

        return $arrParams;
    }

    /**
     * Gets the request data based on the chosen HTTP method
     *
     * @param string $strKey
     *
     * @return  mixed
     */
    private function getRequestData($strKey)
    {
        if ('GET' === $this->psp_http_method) {
            return $_GET[$strKey];
        }

        return $_POST[$strKey];
    }

    /**
     * Gets the raw request data based on the chosen HTTP method
     *
     * @return  array
     */
    private function getRawRequestData()
    {
        if ('GET' === $this->psp_http_method) {
            return $_GET;
        }

        return $_POST;
    }


    /**
     * Validate SHA-OUT signature
     *
     * @return bool
     */
    private function validateSHASign()
    {
        $strSHASign = '';
        $arrParams  = array();

        foreach ($this->getRawRequestData() as $key => $value) {
            if (\in_array(strtoupper($key), static::$arrShaOut)) {
                $arrParams[$key] = $value;
            }
        }

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        foreach ($arrParams as $k => $v) {
            if ($v == '') {
                continue;
            }

            $strSHASign .= strtoupper($k) . '=' . $v . $this->psp_hash_out;
        }

        if ($this->getRequestData('SHASIGN') == strtoupper(hash($this->psp_hash_method, $strSHASign))) {
            return true;
        }

        $this->debugLog(
            sprintf(
                "Received invalid postsale data.\nInput hash: %s\nCalculated hash: %s\nParameters: %s\n",
                $this->getRequestData('SHASIGN'),
                strtoupper(hash($this->psp_hash_method, $strSHASign)),
                print_r($arrParams, true)
            )
        );

        return false;
    }

    /**
     * Return information or advanced features in the backend.
     *
     * @param int $orderId
     *
     * @return string
     */
    public function backendInterface($orderId)
    {
        if (null === ($objOrder = Order::findByPk($orderId))) {

            return parent::backendInterface($orderId);
        }

        $paymentData = json_decode($objOrder->payment_data, true);

        if (0 === \count($paymentData)) {

            return parent::backendInterface($orderId);
        }

        $i = 0;


        $buffer = '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=payment', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'][$this->type][0] . ')' . '</h2>

<table class="tl_show">
  <tbody>';

        foreach ($paymentData as $k => $v) {
            if (\is_array($v)) {
                continue;
            }

            $buffer .= '
  <tr>
    <td' . (($i % 2) ? '' : ' class="tl_bg"') . '><span class="tl_label">' . $k . ': </span></td>
    <td' . (($i % 2) ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

            ++$i;
        }

        $buffer .= '
</tbody></table>
</div>';

        return $buffer;
    }
}
