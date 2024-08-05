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

use Contao\Controller;
use Contao\FrontendUser;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Isotope\CompatibilityHelper;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\Address;
use Isotope\Model\Config;
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

        // Try to load the default member address
        if (null === $objAddress && \Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER')) {
            $objAddress = Address::findDefaultBillingForMember(FrontendUser::getInstance()->id);
        }

        // Try to load the default collection address
        if (null === $objAddress) {
            $objAddress = Address::findDefaultBillingForProductCollection($this->id);
        }

        // Last option: create a new address, including member data if available
        if (null === $objAddress) {
            $objAddress = Address::createForProductCollection(
                $this,
                Isotope::getConfig()->getBillingFields(),
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

        // Try to load the default member address
        if (null === $objAddress && \Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER')) {
            $objAddress = Address::findDefaultShippingForMember(FrontendUser::getInstance()->id);
        }

        // Try to load the default collection address
        if (null === $objAddress) {
            $objAddress = Address::findDefaultShippingForProductCollection($this->id);
        }

        // Last option: create a new address, including member data if available
        if (null === $objAddress) {
            $objAddress = Address::createForProductCollection(
                $this,
                Isotope::getConfig()->getShippingFields(),
                false,
                true
            );
        }

        $this->arrCache['shippingAddress'] = $objAddress;

        return $objAddress;
    }

    /**
     * Merge guest cart if necessary
     *
     * @throws \BadMethodCallException if the product collection is locked.
     */
    public function mergeGuestCart()
    {
        $this->ensureNotLocked();

        $strHash = (string) Input::cookie(static::$strCookie);

        // Temporary cart available, move to this cart. Must be after creating a new cart!
        if (\Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER') && '' !== $strHash && $this->member > 0) {
            $blnMerge = $this->countItems() > 0;
            $objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $this->store_id));

            if (null !== $objTemp) {
                $arrIds = $this->copyItemsFrom($objTemp);

                if ($blnMerge && \count($arrIds) > 0) {
                    Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['cartMerged']);
                }

                $objTemp->delete();
            }

            // Delete cookie
            System::setCookie(static::$strCookie, '', time() - 3600);
            Controller::reload();
        }
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
     * Check if minimum order amount is reached
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (Isotope::getConfig()->cartMinSubtotal > 0 && Isotope::getConfig()->cartMinSubtotal > $this->getSubtotal()) {
            return true;
        }

        return parent::hasErrors();
    }

    /**
     * Get error messages for the cart
     *
     * @return array
     */
    public function getErrors()
    {
        $arrErrors = parent::getErrors();

        if (Isotope::getConfig()->cartMinSubtotal > 0 && Isotope::getConfig()->cartMinSubtotal > $this->getSubtotal()) {
            $arrErrors[] = sprintf(
                $GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'],
                Isotope::formatPriceWithCurrency(Isotope::getConfig()->cartMinSubtotal)
            );
        }

        return $arrErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        parent::save();

        // Create/renew the guest cart cookie
        if (!$this->member && !headers_sent()) {
            System::setCookie(
                static::$strCookie,
                $this->uniqid,
                $this->tstamp + $GLOBALS['TL_CONFIG']['iso_cartTimeout']
            );
        }

        return $this;
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

    /**
     * Load the current cart
     *
     * @return Cart
     */
    public static function findForCurrentStore()
    {
        /** @var PageModel $objPage */
        global $objPage;

        if (!CompatibilityHelper::isFrontend() || null === $objPage || 0 === (int) $objPage->rootId) {
            return null;
        }

        /** @var PageModel|\stdClass $rootPage */
        $rootPage = PageModel::findByPk($objPage->rootId);

        $time       = time();
        $objCart    = null;
        $cookieHash = null;
        $storeId    = (int) $rootPage->iso_store_id;
        $isMember = \Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER');

        if ($isMember) {
            $objCart = static::findOneBy(
                array('tl_iso_product_collection.member=?', 'store_id=?'),
                array(FrontendUser::getInstance()->id, $storeId)
            );
        } else {
            $cookieHash = (string) Input::cookie(static::$strCookie);

            if ('' !== $cookieHash) {
                $objCart = static::findOneBy(array('uniqid=?', 'store_id=?'), array($cookieHash, $storeId));
            }

            if (null === $objCart) {
                $cookieHash = self::generateCookieId();
            }
        }

        // Create new cart
        if ($objCart === null) {
            $objConfig = Config::findByRootPageOrFallback($objPage->rootId);
            $objCart   = new static();

            // Can't call the individual rows here, it would trigger markModified and a save()
            $objCart->setRow(array_merge($objCart->row(), array(
                'tstamp'    => $time,
                'member'    => $isMember ? FrontendUser::getInstance()->id : 0,
                'uniqid'    => $cookieHash,
                'config_id' => $objConfig->id,
                'store_id'  => $storeId,
            )));

            return $objCart;

        }

        $objCart->tstamp = $time;

        // Renew the guest cart cookie
        if (!$objCart->member) {

            // Create a new cookie ID if it's an old sha1() hash
            if (40 == strlen($objCart->uniqid)) {
                $objCart->uniqid = self::generateCookieId();
            }

            if (!headers_sent()) {
                System::setCookie(
                    static::$strCookie,
                    $objCart->uniqid,
                    $time + $GLOBALS['TL_CONFIG']['iso_cartTimeout']
                );
            }
        }

        return $objCart;
    }

    private static function generateCookieId()
    {
        if (!function_exists('random_bytes')) {
            return uniqid('', true);
        }

        try {
            return bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            return uniqid('', true);
        }
    }
}
