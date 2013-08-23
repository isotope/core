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

namespace Isotope;


class Analytics extends Frontend
{

    /**
     * Process checkout
     */
    public function trackOrder($objOrder, $arrItemIds, $arrData)
    {
        $objConfig = new IsotopeConfig();

        if ($objConfig->findBy('id', $objOrder->config_id)) {

            if ($objConfig->ga_enable) {
                $this->trackGATransaction($objConfig, $objOrder);
            }
        }

        return true;
    }


    /**
     * Actually execute the GoogleAnalytics tracking
     * @param Database_Result
     * @param IsotopeProductCollection
     */
    protected function trackGATransaction($objConfig, $objOrder)
    {
        // Initilize GA Tracker
        $tracker = new \UnitedPrototype\GoogleAnalytics\Tracker($objConfig->ga_account, $this->Environment->base);

        // Assemble Visitor information
        // (could also get unserialized from database)
        $visitor = new \UnitedPrototype\GoogleAnalytics\Visitor();
        $visitor->setIpAddress($this->Environment->ip);
        $visitor->setUserAgent($this->Environment->httpUserAgent);

        $transaction = new \UnitedPrototype\GoogleAnalytics\Transaction();

        $transaction->setOrderId($objOrder->order_id);
        $transaction->setAffiliation($objConfig->name);
        $transaction->setTotal($objOrder->grandTotal);
        $transaction->setTax($objOrder->taxTotal);
        $transaction->setShipping($objOrder->shippingTotal);
        $transaction->setCity($objOrder->billing_address['city']);

        if ($objOrder->billing_address['subdivision']) {
            $arrSub = explode("-",$objOrder->billing_address['subdivision']);
            $transaction->setRegion($arrSub[1]);
        }

        $transaction->setCountry($objOrder->billing_address['country']);

        $arrProducts = $objOrder->getProducts();



        foreach ($arrProducts as $i=>$objProduct)
        {
            $item = new \UnitedPrototype\GoogleAnalytics\Item();

            $arrOptions = array();
            $arrOptionValues = array();

            if ($objProduct->sku) {
                $item->setSku($objProduct->sku);
            }

            $item->setName($objProduct->name);
            $item->setPrice($objProduct->price);
            $item->setQuantity($objProduct->quantity_requested);

            //Do we also potentially have options?
            $arrOptions = $objProduct->getOptions(true);

            foreach ($arrOptions as $field => $value)
            {
                if ($value == '')
                    continue;

                $arrOptionValues[] = $this->Isotope->formatValue('tl_iso_products', $field, $value);

            }

            if(count($arrOptionValues))
                $item->setVariation(implode(' ',$arrOptionValues));

            $transaction->addItem($item);
        }

        // Track logged-in member as custom variable
        if ($objConfig->ga_member != '' && FE_USER_LOGGED_IN)
        {
            $this->import('FrontendUser', 'User');

            $customVar = new \UnitedPrototype\GoogleAnalytics\CustomVariable(1, 'Member', $this->parseSimpleTokens($objConfig->ga_member, $this->User->getData()), \UnitedPrototype\GoogleAnalytics\CustomVariable::SCOPE_VISITOR);

            $tracker->addCustomVariable($customVar);
        }

        // Assemble Session information
        // (could also get unserialized from PHP session)
        $session = new \UnitedPrototype\GoogleAnalytics\Session();

        $tracker->trackTransaction($transaction, $session, $visitor);
    }
}
