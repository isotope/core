<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollection;

use Contao\System;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection;

/**
 * Class Cart provides methods to handle Isotope cart.
 */
class Cart extends ProductCollection implements IsotopeOrderableCollection
{
    /**
     * Cookie hash value
     * @var string
     */
    protected $strHash = '';

    /**
     * Name of the temporary cart cookie
     * @var string
     */
    protected static $strCookie = 'ISOTOPE_TEMP_CART';

    /**
     * Draft of Order for this cart
     * @var Order
     */
    protected $objDraftOrder;

    /**
     * A cart does not have a payment method,
     * but the order might require payment for surcharges (e.g. shipping)
     */
    public function requiresPayment()
    {
        $draftOrder = $this->getDraftOrder();

        if (null !== $draftOrder) {
            return $draftOrder->requiresPayment();
        }

        return parent::requiresPayment();
    }

    /**
     * Get billing address or create if none exists
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        if (!empty($this->arrCache['billingAddress'])) {
            return $this->arrCache['billingAddress'];
        }

        $objAddress = parent::getBillingAddress();

        // Create a new address, including member data if available
        if (null === $objAddress) {
            /** @var StoreManager $storeManager */
            $storeManager = System::getContainer()->get(StoreManager::class);
            $context = $storeManager->getCurrentContext();

            $objAddress = Address::createForProductCollection(
                $this,
                $context->getBillingAddressConfig()->getAddressFields(),
                true
            );
        }

        $this->arrCache['billingAddress'] = $objAddress;

        return $objAddress;
    }

    /**
     * Get shipping address or create if none exists
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        if (!empty($this->arrCache['shippingAddress'])) {
            return $this->arrCache['shippingAddress'];
        }

        $objAddress = parent::getShippingAddress();

        // Create a new address, including member data if available
        if (null === $objAddress) {
            /** @var StoreManager $storeManager */
            $storeManager = System::getContainer()->get(StoreManager::class);
            $context = $storeManager->getCurrentContext();

            $objAddress = Address::createForProductCollection(
                $this,
                $context->getShippingAddressConfig()->getAddressFields(),
                false,
                true
            );
        }

        $this->arrCache['shippingAddress'] = $objAddress;

        return $objAddress;
    }

    /**
     * Get and update order draft for current cart or create one if it does not yet exist
     *
     * @return Order
     */
    public function getDraftOrder()
    {
        if ($this->objDraftOrder === null) {
            $t = Order::getTable();

            $objOrder = Order::findOneBy(
                array(
                    "$t.source_collection_id=?",
                    "$t.locked IS NULL"
                ),
                array($this->id)
            );

            if (null === $objOrder) {
                $objOrder = Order::createFromCollection($this);
            }

            try {
                $objOrder->config_id = (int) $this->config_id;
                $objOrder->store_id  = (int) $this->store_id;
                $objOrder->member    = (int) $this->member;

                $objOrder->setShippingMethod($this->getShippingMethod());
                $objOrder->setPaymentMethod($this->getPaymentMethod());

                $objOrder->setBillingAddress($this->getBillingAddress());

                if ($this->shipping_address_id) {
                    $objOrder->setShippingAddress($this->getShippingAddress());
                } else {
                    $objOrder->setShippingAddress($this->getBillingAddress());
                }

                $objOrder->purge();
                $arrItemIds = $objOrder->copyItemsFrom($this);

                $objOrder->updateDatabase();

                // HOOK: order status has been updated
                if (isset($GLOBALS['ISO_HOOKS']['updateDraftOrder'])
                    && \is_array($GLOBALS['ISO_HOOKS']['updateDraftOrder'])
                ) {
                    foreach ($GLOBALS['ISO_HOOKS']['updateDraftOrder'] as $callback) {
                        System::importStatic($callback[0])->{$callback[1]}($objOrder, $this, $arrItemIds);
                    }
                }
            } catch (\Exception $e) {
                $objOrder = null;
            }

            $this->objDraftOrder = $objOrder;
        }

        return $this->objDraftOrder;
    }

    /**
     * Get a collection-specific error message for items with errors
     *
     * @return string
     */
    protected function getMessageIfErrorsInItems()
    {
        return $GLOBALS['TL_LANG']['ERR']['cartErrorInItems'];
    }

    /**
     * Clear all cache properties
     */
    protected function clearCache()
    {
        parent::clearCache();

        $this->objDraftOrder = null;
    }
}
