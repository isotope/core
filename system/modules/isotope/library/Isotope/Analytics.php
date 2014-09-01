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

use Isotope\Model\Config;
use Isotope\Model\ProductCollection\Order;


class Analytics extends Frontend
{

    /**
     * Process checkout
     */
    public function trackOrder(Order $objOrder, $arrTokens)
    {
        $objConfig = Config::findByPk($objOrder->config_id);

        if (null !== $objConfig) {

            if ($objConfig->ga_enable) {
                $this->trackGATransaction($objConfig, $objOrder);
            }
        }

        return true;
    }

    /**
     * Actually execute the GoogleAnalytics tracking
     * @param Database_Result
     * @param IsotopeProductCollection $objOrder
     */
    protected function trackGATransaction($objConfig, $objOrder)
    {
        // Initilize GA Tracker
        $tracker = new \UnitedPrototype\GoogleAnalytics\Tracker($objConfig->ga_account, \Environment::get('base'));

        // Assemble Visitor information
        // (could also get unserialized from database)
        $visitor = new \UnitedPrototype\GoogleAnalytics\Visitor();
        $visitor->setIpAddress(\Environment::get('ip'));
        $visitor->setUserAgent(\Environment::get('httpUserAgent'));

        $transaction = new \UnitedPrototype\GoogleAnalytics\Transaction();

        $transaction->setOrderId($objOrder->order_id);
        $transaction->setAffiliation($objConfig->name);
        $transaction->setTotal($objOrder->getTotal());
        $transaction->setTax(($objOrder->getTotal() - $objOrder->getTaxFreeTotal()));
//        $transaction->setShipping($objOrder->shippingTotal);

        $objAddress = $objOrder->getBillingAddress();

        $transaction->setCity($objAddress->city);

        if ($objAddress->subdivision) {
            $arrSub = explode("-", $objAddress->subdivision, 2);
            $transaction->setRegion($arrSub[1]);
        }

        $transaction->setCountry($objAddress->country);

        /** @var \Isotope\Model\ProductCollectionItem $objItem */
        foreach ($objOrder->getItems() as $objItem)
        {
            $item = new \UnitedPrototype\GoogleAnalytics\Item();

            if ($objItem->getSku()) {
                $item->setSku($objItem->getSku());
            } else {
                $item->setSku('product'.$objItem->product_id);
            }

            $item->setName($objItem->getName());
            $item->setPrice($objItem->getPrice());
            $item->setQuantity($objItem->quantity);

            $arrOptionValues = array();
            foreach ($objItem->getConfiguration() as $option) {
                $arrOptionValues[] = (string) $option;
            }

            if (!empty($arrOptionValues)) {
                $item->setVariation(implode(', ', $arrOptionValues));
            }

            $transaction->addItem($item);
        }

        // Track logged-in member as custom variable
        if ($objConfig->ga_member != '' && $objOrder->member > 0 && ($objMember = \MemberModel::findByPk($objOrder->member)) !== null)
        {
            $customVar = new \UnitedPrototype\GoogleAnalytics\CustomVariable(1, 'Member', $this->parseSimpleTokens($objConfig->ga_member, $objMember->row()), \UnitedPrototype\GoogleAnalytics\CustomVariable::SCOPE_VISITOR);

            $tracker->addCustomVariable($customVar);
        }

        // Assemble Session information
        // (could also get unserialized from PHP session)
        $session = new \UnitedPrototype\GoogleAnalytics\Session();

        $tracker->trackTransaction($transaction, $session, $visitor);
    }
}
