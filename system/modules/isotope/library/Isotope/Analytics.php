<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Environment;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection\Order;
use UnitedPrototype\GoogleAnalytics\CustomVariable;
use UnitedPrototype\GoogleAnalytics\Session;
use UnitedPrototype\GoogleAnalytics\Tracker;
use UnitedPrototype\GoogleAnalytics\Transaction;
use UnitedPrototype\GoogleAnalytics\Visitor;


class Analytics extends Frontend
{

    /**
     * Process checkout
     *
     *
     * @return bool
     */
    public function trackOrder(Order $objOrder)
    {
        $objConfig = $objOrder->getConfig();

        if (null !== $objConfig && $objConfig->ga_enable) {
            $this->trackGATransaction($objConfig, $objOrder);
        }

        return true;
    }

    /**
     * Actually execute the GoogleAnalytics tracking
     */
    protected function trackGATransaction(Config $objConfig, IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopeOrderableCollection) {
            return;
        }

        // Initilize GA Tracker
        $tracker = new Tracker($objConfig->ga_account, Environment::get('base'));

        // Assemble Visitor information
        // (could also get unserialized from database)
        $visitor = new Visitor();
        $visitor->setIpAddress(Environment::get('ip'));
        $visitor->setUserAgent(Environment::get('httpUserAgent'));

        $transaction = new Transaction();

        $transaction->setOrderId($objOrder->getDocumentNumber());
        $transaction->setAffiliation($objConfig->name);
        $transaction->setTotal($objOrder->getTotal());
        $transaction->setTax($objOrder->getTotal() - $objOrder->getTaxFreeTotal());
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
        if ($objConfig->ga_member != '' && null !== $objOrder->getMember())
        {
            $customVar = new CustomVariable(
                1,
                'Member',
                \Contao\System::getContainer()->get('contao.string.simple_token_parser')->parse($objConfig->ga_member, $objOrder->getMember()->row()),
                CustomVariable::SCOPE_VISITOR
            );

            $tracker->addCustomVariable($customVar);
        }

        // Assemble Session information
        // (could also get unserialized from PHP session)
        $session = new Session();

        $tracker->trackTransaction($transaction, $session, $visitor);
    }
}
