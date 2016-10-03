<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;

/**
 * PayOne payment method
 *
 * @property string $payone_clearingtype
 * @property string $payone_aid
 * @property string $payone_portalid
 * @property string $payone_key
 */
class Payone extends Postsale
{

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $mode     = (string) \Input::post('mode');
        $txaction = (string) \Input::post('txaction');

        if (\Input::post('aid') != $this->payone_aid
            || \Input::post('portalid') != $this->payone_portalid
            || (!$this->debug && 'test' === $mode)
            || ($this->debug && 'live' === $mode)
        ) {
            \System::log('PayOne configuration mismatch', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        // Ignore all except these actions
        if ('appointed' !== $txaction && 'capture' !== $txaction && 'paid' !== $txaction) {
            die('TSOK');
        }

        if (\Input::post('currency') != $objOrder->getCurrency() || $objOrder->getTotal() != \Input::post('price')) {
            \System::log('PayOne order data mismatch for Order ID "' . \Input::post('reference') . '"', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        if (!$objOrder->checkout()) {
            \System::log('Postsale checkout for Order ID "' . \Input::post('reference') . '" failed', __METHOD__, TL_ERROR);
            die('TSOK');
        }

        if ('paid' === \Input::post('txaction') && \Input::post('balance') == 0) {
            $objOrder->setDatePaid(time());
        }

        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        // PayOne must get TSOK as return value, otherwise the request will be sent again
        die('TSOK');
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) \Input::post('reference'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $i = 0;

        $arrData = [
            'aid'               => $this->payone_aid,
            'portalid'          => $this->payone_portalid,
            'mode'              => $this->debug ? 'test' : 'live',
            'request'           => 'auth' === $this->trans_type ? 'preauthorization' : 'authorization',
            'encoding'          => 'UTF-8',
            'clearingtype'      => $this->payone_clearingtype,
            'reference'         => $objOrder->getId(),
            'display_name'      => 'no',
            'display_address'   => 'no',
            'successurl'        => \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder),
            'backurl'           => \Environment::get('base') . Checkout::generateUrlForStep('failed'),
            'amount'            => $objOrder->getTotal() * 100,
            'currency'          => $objOrder->getCurrency(),

            // Custom parameter to recognize payone in postsale request (only alphanumeric is allowed)
            'param'             => 'paymentMethodPayone' . $this->id
        ];

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
                        $option = $option['label'] . ': ' . (string) $option;
                    }
                );

                $strConfig = ' (' . implode(', ', $arrConfig) . ')';
            }

            $arrData['id[' . ++$i . ']'] = $objItem->getSku();
            $arrData['pr[' . $i . ']']   = round($objItem->getPrice(), 2) * 100;
            $arrData['no[' . $i . ']']   = $objItem->quantity;
            $arrData['de[' . $i . ']']   = $this->escapeParameterValue($objItem->getName().$strConfig);
        }

        foreach ($objOrder->getSurcharges() as $k => $objSurcharge) {

            if (!$objSurcharge->addToTotal) {
                continue;
            }

            $arrData['id[' . ++$i . ']'] = 'surcharge' . $k;
            $arrData['pr[' . $i . ']']   = $objSurcharge->total_price * 100;
            $arrData['no[' . $i . ']']   = '1';
            $arrData['de[' . $i . ']']   = $this->escapeParameterValue($objSurcharge->label);
        }

        ksort($arrData);
        // Do not urlencode values because Payone does not properly decode POST values (whatever...)
        $strHash = md5(implode('', $arrData) . $this->payone_key);

        /** @var Template|\stdClass $objTemplate */
        $objTemplate                  = new Template('iso_payment_payone');
        $objTemplate->id              = $this->id;
        $objTemplate->data            = $arrData;
        $objTemplate->hash            = $strHash;
        $objTemplate->billing_address = $objOrder->getBillingAddress()->row();
        $objTemplate->headline        = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message         = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel          = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * Escape the parameter value so it's correctly handled by Payone
     *
     * @param string $value
     *
     * @return string
     */
    protected function escapeParameterValue($value)
    {
        $value = strip_insert_tags(strip_tags($value));
        $value = \StringUtil::restoreBasicEntities($value);
        $value = html_entity_decode($value);
        $value = str_replace("\xC2\xA0", ' ', $value); // strip &nbsp;
        $value = str_replace('"', '\'', $value); // replace double quotes with single quotes

        return $value;
    }
}
