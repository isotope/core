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

use Contao\Template;
use Haste\Form\Form;
use Haste\Util\StringUtil;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionSurcharge\Shipping;
use Isotope\Model\ProductCollectionSurcharge\Tax;

class BillpayWithSaferpay extends Saferpay
{
    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        $objBillingAddress = Isotope::getCart()->getBillingAddress();
        $objShippingAddress = Isotope::getCart()->getShippingAddress();

        if (null === $objBillingAddress || !\in_array($objBillingAddress->country, array('de', 'ch', 'at'), true)) {
            return false;
        }

        // Billpay is not supported when billing and shipping address are not equal
        if (Isotope::getCart()->hasShipping() && $objBillingAddress->id != $objShippingAddress->id) {
            return false;
        }

        return parent::isAvailable();
    }

    /**
     * Automatically add Billpay conditions to checkout form
     *
     * @param Form $objForm
     */
    public static function addOrderCondition(Form $objForm)
    {
        if (Isotope::getCart()->hasPayment() && Isotope::getCart()->getPaymentMethod() instanceof BillpayWithSaferpay) {

            $strLabel = $GLOBALS['TL_LANG']['MSC']['billpay_agb_'.Isotope::getCart()->getBillingAddress()->country];

            if ($strLabel == '') {
                throw new \LogicException('Missing BillPay AGB for country "' . Isotope::getCart()->getBillingAddress()->country . '" and language "' . $GLOBALS['TL_LANGUAGE'] . '"');
            }

            $objForm->addFormField(
                'billpay_confirmation',
                array(
                    'label' => array('', $strLabel),
                    'inputType' => 'checkbox',
                    'eval' => array('mandatory'=>true)
                )
            );
        }
    }

    /**
     * Add additional functionality for Billpay to document template
     *
     * @param \Template                $objTemplate
     * @param IsotopeProductCollection $objCollection
     */
    public function addToDocumentTemplate(Template $objTemplate, IsotopeProductCollection $objCollection)
    {
        $objTemplate->billpay = false;

        /** @var Order $objCollection */
        if ($objCollection instanceof Order
            && $objCollection->hasPayment()
            && $objCollection->getPaymentMethod() instanceof BillpayWithSaferpay
        ) {
            $arrPayment = \Contao\StringUtil::deserialize($objCollection->payment_data);

            if (!empty($arrPayment) && \is_array($arrPayment) && \is_array($arrPayment['POSTSALE'])) {
                $doc = new \DOMDocument();
                $doc->loadXML(end($arrPayment['POSTSALE']));
                $this->objXML = $doc->getElementsByTagName('IDP')->item(0)->attributes;

                $objTemplate->billpay = true;
                $objTemplate->billpay_accountholder = $this->getPostValue('POB_ACCOUNTHOLDER');
                $objTemplate->billpay_accountnumber = $this->getPostValue('POB_ACCOUNTNUMBER');
                $objTemplate->billpay_bankcode = $this->getPostValue('POB_BANKCODE');
                $objTemplate->billpay_bankname = $this->getPostValue('POB_BANKNAME');
                $objTemplate->billpay_payernote = $this->getPostValue('POB_PAYERNOTE');
            }
        }
    }

    /**
     * Add BillPay-specific data to POST values
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return array
     */
    protected function generatePaymentPostData(IsotopeProductCollection $objOrder)
    {
        $arrData = parent::generatePaymentPostData($objOrder);

        if (!$objOrder instanceof IsotopeOrderableCollection) {
            return $arrData;
        }

        // Billing address
        $objBillingAddress = $objOrder->getBillingAddress();
        $this->addAddress($arrData, $objBillingAddress);
        $arrData['EMAIL']       = (string) $objBillingAddress->email;
        $arrData['DATEOFBIRTH'] = ($objBillingAddress->dateOfBirth ? date('Ymd', $objBillingAddress->dateOfBirth) : '');
        $arrData['COMPANY']     = (string) $objBillingAddress->company;

        // Shipping address
        $objShippingAddress = $objOrder->getShippingAddress();
        $this->addAddress($arrData, $objShippingAddress, 'DELIVERY_');

        // Cart items and total
        $arrData['BASKETDATA'] = $this->getCollectionItemsAsXML($objOrder);
        $arrData['BASKETTOTAL'] = $this->getCollectionTotalAsXML($objOrder);

        // Remove empty parameters
        $arrData = array_filter($arrData);

        return $arrData;
    }

    /**
     * Generate XML data for collection items
     *
     * @param IsotopeOrderableCollection $objCollection
     *
     * @return string
     */
    private function getCollectionItemsAsXML(IsotopeOrderableCollection $objCollection)
    {
        $xml = new \DOMDocument();
        $articleData = $xml->createElement('article_data');

        foreach ($objCollection->getItems() as $objItem) {
            $article = $xml->createElement('article');

            $id = $xml->createAttribute('articleid');
            $id->value = $objItem->getSku();
            $article->appendChild($id);

            $quantity = $xml->createAttribute('articlequantity');
            $quantity->value = $objItem->quantity;
            $article->appendChild($quantity);

            $name = $xml->createAttribute('articlename');
            $name->value = StringUtil::convertToText(
                $objItem->getName(),
                StringUtil::NO_TAGS | StringUtil::NO_BREAKS | StringUtil::NO_INSERTTAGS | StringUtil::NO_ENTITIES
            );
            $article->appendChild($name);

            $price = $xml->createAttribute('articleprice');
            $price->value = round($objItem->getTaxFreePrice() * 100);
            $article->appendChild($price);

            $grossPrice = $xml->createAttribute('articlepricegross');
            $grossPrice->value = round($objItem->getPrice() * 100);
            $article->appendChild($grossPrice);

            $articleData->appendChild($article);
        }

        foreach ($objCollection->getSurcharges() as $objSurcharge) {
            if ($objSurcharge->total_price > 0 && !($objSurcharge instanceof Shipping) && !($objSurcharge instanceof Tax)) {

                $article = $xml->createElement('article');

                // Quantity is always 1 because we only have a total price
                $quantity = $xml->createAttribute('articlequantity');
                $quantity->value = 1;
                $article->appendChild($quantity);

                $name = $xml->createAttribute('articlename');
                $name->value = StringUtil::convertToText(
                    $objSurcharge->label,
                    StringUtil::NO_TAGS | StringUtil::NO_BREAKS | StringUtil::NO_INSERTTAGS | StringUtil::NO_ENTITIES
                );
                $article->appendChild($name);

                $price = $xml->createAttribute('articleprice');
                $price->value = round($objSurcharge->tax_free_total_price * 100);
                $article->appendChild($price);

                $grossPrice = $xml->createAttribute('articlepricegross');
                $grossPrice->value = round($objSurcharge->total_price * 100);
                $article->appendChild($grossPrice);

                $articleData->appendChild($article);
            }
        }

        $xml->appendChild($articleData);

        return $xml->saveXML($xml->documentElement);
    }


    /**
     * @param IsotopeOrderableCollection $objCollection
     *
     * @return string
     */
    private function getCollectionTotalAsXML(IsotopeOrderableCollection $objCollection)
    {
        $intRebate = 0;
        $intRebateGross = 0;
        $strShippingName = '';
        $intShippingPrice = 0;
        $intShippingPriceGross = 0;

        foreach ($objCollection->getSurcharges() as $objSurcharge) {
            if ($objSurcharge->total_price < 0) {
                $intRebate += round($objSurcharge->tax_free_total_price * 100);
                $intRebateGross += round($objSurcharge->total_price * 100);
            } elseif ($objSurcharge instanceof Shipping) {
                $strShippingName = $objSurcharge->label;
                $intShippingPrice += round($objSurcharge->tax_free_total_price * 100);
                $intShippingPriceGross += round($objSurcharge->total_price * 100);
            }
        }

        $xml = new \DOMDocument();
        $total = $xml->createElement('total');

        if ($intShippingPrice != 0 || $intShippingPriceGross != 0) {
            $shippingName = $xml->createAttribute('shippingname');
            $shippingName->value = StringUtil::convertToText(
                $strShippingName,
                StringUtil::NO_TAGS | StringUtil::NO_BREAKS | StringUtil::NO_INSERTTAGS | StringUtil::NO_ENTITIES
            );
            $total->appendChild($shippingName);

            $shippingPrice = $xml->createAttribute('shippingprice');
            $shippingPrice->value = $intShippingPrice;
            $total->appendChild($shippingPrice);

            $shippingPriceGross = $xml->createAttribute('shippingpricegross');
            $shippingPriceGross->value = $intShippingPriceGross;
            $total->appendChild($shippingPriceGross);
        }

        if ($intRebate != 0 || $intRebateGross != 0) {
            $rebate = $xml->createAttribute('rebate');
            $rebate->value = $intRebate;
            $total->appendChild($rebate);

            $rebateGross = $xml->createAttribute('rebategross');
            $rebateGross->value = $intRebateGross;
            $total->appendChild($rebateGross);
        }

        $cartTotalPrice = $xml->createAttribute('carttotalprice');
        $cartTotalPrice->value = round($objCollection->getTaxFreeTotal() * 100);
        $total->appendChild($cartTotalPrice);

        $cartTotalPriceGross = $xml->createAttribute('carttotalpricegross');
        $cartTotalPriceGross->value = round($objCollection->getTotal() * 100);
        $total->appendChild($cartTotalPriceGross);

        $currency = $xml->createAttribute('currency');
        $currency->value = $objCollection->getCurrency();
        $total->appendChild($currency);

        $xml->appendChild($total);

        return $xml->saveXML($xml->documentElement);
    }

    private function addAddress(&$data, Address $address, $prefix = '')
    {
        $data[$prefix . 'GENDER']          = substr((string) $address->gender, 0, 1);
        $data[$prefix . 'FIRSTNAME']       = (string) $address->firstname;
        $data[$prefix . 'LASTNAME']        = (string) $address->lastname;
        $data[$prefix . 'STREET']          = (string) $address->street_1;
        $data[$prefix . 'ADDRESSADDITION'] = (string) $address->street_2;
        $data[$prefix . 'ZIP']             = (string) $address->postal;
        $data[$prefix . 'CITY']            = (string) $address->city;
        $data[$prefix . 'COUNTRY']         = strtoupper((string) $address->country);
        $data[$prefix . 'PHONE']           = (string) $address->phone;
    }
}
