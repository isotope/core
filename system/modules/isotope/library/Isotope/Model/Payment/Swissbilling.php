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
use Contao\System;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollectionSurcharge\Payment as PaymentSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Shipping as ShippingSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Tax;
use Isotope\Model\TaxRate;
use Isotope\Module\Checkout;
use Isotope\Template;
use Terminal42\SwissbillingApi\ApiFactory;
use Terminal42\SwissbillingApi\Client;
use Terminal42\SwissbillingApi\Exception\SoapException;
use Terminal42\SwissbillingApi\Type\DateTime;
use Terminal42\SwissbillingApi\Type\Debtor;
use Terminal42\SwissbillingApi\Type\InvoiceItem;
use Terminal42\SwissbillingApi\Type\Merchant;
use Terminal42\SwissbillingApi\Type\Transaction;

/**
 * SWISSBILLING payment method
 *
 * @property string $swissbilling_id
 * @property string $swissbilling_pwd
 * @property bool   $swissbilling_b2b
 * @property bool   $swissbilling_prescreening
 */
class Swissbilling extends Payment
{
    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        $cart = Isotope::getCart();

        if (null === $cart) {
            return false;
        }

        if ($cart->hasShipping()
            && $cart->getBillingAddress()->id !== $cart->getShippingAddress()->id
        ) {
            return false;
        }

        if ('ch' !== $cart->getBillingAddress()->country) {
            return false;
        }

        if ('CHF' !== $cart->getCurrency()) {
            return false;
        }

        if (!$cart->requiresShipping()) {
            return false;
        }

        if (!parent::isAvailable()) {
            return false;
        }

        if ($this->swissbilling_prescreening) {
            try {
                return $this->getClient($cart)->preScreening(
                    $this->getTransaction($cart),
                    $this->getDebtor($cart),
                    $this->getItems($cart)
                )->isAnswered();
            } catch (SoapException $e) {
                return false;
            }
        }

        return true;
    }

    public function processPayment(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        if ($objOrder->isCheckoutComplete()) {
            return true;
        }

        if (!($timestamp = Input::get('timestamp'))) {
            return false;
        }

        $swissbilling = $this->getClient($objOrder);
        $timestamp = DateTime::create($timestamp);

        try {
            $transaction = $swissbilling->confirmation($objOrder->getId(), $timestamp);

            if ($transaction->isAnswered() || $transaction->isAcknowledged()) {
                if ('capture' === $this->trans_type && !$transaction->isAcknowledged()) {
                    $swissbilling->acknowledge($objOrder->getId(), $timestamp);
                }

                return true;
            }
        } catch (SoapException $exception) {
            $this->debugLog($exception);
        }

        return false;
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

        try {
            $transaction = $this->getTransaction($objOrder);
            $status = $this->getClient($objOrder, $transaction->order_timestamp)->request(
                $transaction,
                $this->getDebtor($objOrder),
                $this->getItems($objOrder)
            );

            $this->debugLog($status);

            if ($status->hasError()) {
                return false;
            }
        } catch (SoapException $e) {
            $this->debugLog('EshopTransactionRequest() caused exception');
            $this->debugLog($e);
            return false;
        }

        $objTemplate = new Template('iso_payment_swissbilling');
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1];
        $objTemplate->link = $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2];
        $objTemplate->url = $status->url;

        return $objTemplate->parse();
    }

    private function getTransaction(IsotopeOrderableCollection $collection)
    {
        $vat = 0;
        $admin = 0;
        $delivery = 0;
        $discount = 0;

        foreach ($collection->getSurcharges() as $surcharge) {
            if ($surcharge->total_price < 0) {
                $discount -= $surcharge->total_price;
                continue;
            }

            switch (true) {
                case $surcharge instanceof ShippingSurcharge:
                    $delivery += $surcharge->total_price;
                    break;

                case $surcharge instanceof Tax:
                    $vat += $surcharge->total_price;
                    break;

                case $surcharge instanceof PaymentSurcharge:
                default:
                    $admin += $surcharge->total_price;
                    break;
            }
        }

        $transaction = new Transaction();
        $transaction->is_B2B = (bool) $this->swissbilling_b2b && !empty($collection->getBillingAddress()->company);
        $transaction->eshop_ID = $collection->getStoreId();
        $transaction->eshop_ref = $collection->getId();
        $transaction->order_timestamp = new DateTime(new \DateTime());
        $transaction->amount = $collection->getTotal();
        $transaction->VAT_amount = $vat;
        $transaction->admin_fee_amount = $admin;
        $transaction->delivery_fee_amount = $delivery;
        $transaction->vol_discount = 0;
        $transaction->coupon_discount_amount = $discount;
        $transaction->phys_delivery = true;
        $transaction->debtor_IP = Environment::get('ip');

        return $transaction;
    }

    private function getDebtor(IsotopeOrderableCollection $collection)
    {
        $billingAddress = $collection->getBillingAddress();

        if (!$billingAddress instanceof Address) {
            throw new \RuntimeException('Invalid billing address');
        }

        $debtor = new Debtor();
        $debtor->title = '';
        $debtor->company_name = $billingAddress->company;
        $debtor->firstname = $billingAddress->firstname;
        $debtor->lastname = $billingAddress->lastname;
        $debtor->birthdate = '1970-01-01';
        $debtor->adr1 = $billingAddress->street_1;
        $debtor->adr2 = $billingAddress->street_2;
        $debtor->city = $billingAddress->city;
        $debtor->zip = $billingAddress->postal;
        $debtor->country = 'CH';
        $debtor->email = $billingAddress->email;
        $debtor->phone = $billingAddress->phone;
        $debtor->language = strtoupper($GLOBALS['TL_LANGUAGE']);

        if ($member = $collection->getMember()) {
            $debtor->user_ID = $member->id;
        }

        return $debtor;
    }

    private function getItems(IsotopeOrderableCollection $collection)
    {
        $data = [];

        foreach ($collection->getItems() as $item) {
            $taxClass = $item->getProduct()->getPrice($collection)->getRelated('tax_class');
            $arrAddresses = array(
                'billing'  => $collection->getBillingAddress(),
                'shipping' => $collection->getShippingAddress(),
            );

            /** @var TaxRate $taxRate */
            if (($taxRate = $taxClass->getRelated('includes')) !== null && $taxRate->isApplicable($item->price, $arrAddresses)) {
                $vatRate = $taxRate->getAmount();
            } else if (($taxRates = $this->getRelated('rates')) !== null) {
                foreach ($taxRates as $taxRate) {
                    if ($taxRate->isApplicable($item->price, $arrAddresses)) {
                        $vatRate = $taxRate->getAmount();
                        break;
                    }
                }
            }

            $invoiceItem = new InvoiceItem();
            $invoiceItem->desc = $item->getName();
            $invoiceItem->short_desc = $item->getName();
            $invoiceItem->quantity = $item->quantity;
            $invoiceItem->unit_price = $item->getPrice();
            $invoiceItem->VAT_rate = $vatRate;
            $invoiceItem->VAT_amount = $item->getPrice() - $item->getTaxFreePrice();

            $data[] = $invoiceItem;
        }

        return $data;
    }

    private function getClient(IsotopeOrderableCollection $collection, DateTime $timestamp = null): Client
    {
        $returnUrl = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $collection, null, true);

        if ($timestamp) {
            $returnUrl = Url::addQueryString('timestamp='.$timestamp, $returnUrl);
        }

        $merchant = new Merchant(
            $this->swissbilling_id,
            $this->swissbilling_pwd,
            $returnUrl,
            $returnUrl,
            $returnUrl
        );

        $factory = new ApiFactory(!$this->debug);

        return new Client($factory, $merchant);
    }
}
