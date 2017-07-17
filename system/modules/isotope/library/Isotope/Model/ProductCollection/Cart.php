<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollection;

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
     * Get billing address or create if none exists
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        $objAddress = parent::getBillingAddress();

        // Try to load the default member address
        if (null === $objAddress && FE_USER_LOGGED_IN === true) {
            $objAddress = Address::findDefaultBillingForMember(\FrontendUser::getInstance()->id);
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

        return $objAddress;
    }

    /**
     * Get shipping address or create if none exists
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        $objAddress = parent::getShippingAddress();

        // Try to load the default member address
        if (null === $objAddress && FE_USER_LOGGED_IN === true) {
            $objAddress = Address::findDefaultShippingForMember(\FrontendUser::getInstance()->id);
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

        $strHash = (string) \Input::cookie(static::$strCookie);

        // Temporary cart available, move to this cart. Must be after creating a new cart!
        if (FE_USER_LOGGED_IN === true && '' !== $strHash && $this->member > 0) {
            $blnMerge = $this->countItems() > 0;
            $objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $this->store_id));

            if (null !== $objTemp) {
                $arrIds = $this->copyItemsFrom($objTemp);

                if ($blnMerge && count($arrIds) > 0) {
                    Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['cartMerged']);
                }

                $objTemp->delete();
            }

            // Delete cookie
            \System::setCookie(static::$strCookie, '', time() - 3600, $GLOBALS['TL_CONFIG']['websitePath']);
            \Controller::reload();
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

            if (null !== $objOrder) {
                try {
                    $objOrder->config_id = (int) $this->config_id;
                    $objOrder->store_id  = (int) $this->store_id;
                    $objOrder->member    = (int) $this->member;

                    $objOrder->setShippingMethod($this->getShippingMethod());
                    $objOrder->setPaymentMethod($this->getPaymentMethod());

                    $objOrder->setShippingAddress($this->getShippingAddress());
                    $objOrder->setBillingAddress($this->getBillingAddress());

                    $objOrder->purge();
                    $arrItemIds = $objOrder->copyItemsFrom($this);

                    $objOrder->updateDatabase();

                    // HOOK: order status has been updated
                    if (isset($GLOBALS['ISO_HOOKS']['updateDraftOrder'])
                        && is_array($GLOBALS['ISO_HOOKS']['updateDraftOrder'])
                    ) {
                        foreach ($GLOBALS['ISO_HOOKS']['updateDraftOrder'] as $callback) {
                            \System::importStatic($callback[0])->{$callback[1]}($objOrder, $this, $arrItemIds);
                        }
                    }
                } catch (\Exception $e) {
                    $objOrder = null;
                }
            }

            if (null === $objOrder) {
                $objOrder = Order::createFromCollection($this);
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

        if (!$this->member) {
            \System::setCookie(
                static::$strCookie,
                $this->uniqid,
                $this->tstamp + $GLOBALS['TL_CONFIG']['iso_cartTimeout'],
                $GLOBALS['TL_CONFIG']['websitePath']
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
        /** @var \PageModel $objPage */
        global $objPage;

        if ('FE' !== TL_MODE || null === $objPage || 0 === (int) $objPage->rootId) {
            return null;
        }

        /** @var \PageModel|\stdClass $rootPage */
        $rootPage = \PageModel::findByPk($objPage->rootId);

        $time       = time();
        $objCart    = null;
        $storeId    = (int) $rootPage->iso_store_id;

        if (true === FE_USER_LOGGED_IN) {
            $cookieHash = null;

            $objCart = static::findOneBy(
                array('member=?', 'store_id=?'),
                array(\FrontendUser::getInstance()->id, $storeId)
            );

        } else {
            $cookieHash = (string) \Input::cookie(static::$strCookie);

            if ('' === $cookieHash) {
                $cookieHash = sha1(
                    session_id()
                    . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? \Environment::get('ip') : '')
                    . $storeId
                    . static::$strCookie
                );
            }

            $objCart = static::findOneBy(array('uniqid=?', 'store_id=?'), array($cookieHash, $storeId));
        }

        // Create new cart
        if ($objCart === null) {
            $objConfig = Config::findByRootPageOrFallback($objPage->rootId);
            $objCart   = new static();

            // Can't call the individual rows here, it would trigger markModified and a save()
            $objCart->setRow(array_merge($objCart->row(), array(
                'tstamp'    => $time,
                'member'    => FE_USER_LOGGED_IN === true ? \FrontendUser::getInstance()->id : 0,
                'uniqid'    => $cookieHash,
                'config_id' => $objConfig->id,
                'store_id'  => $storeId,
            )));

        } else {
            $objCart->tstamp = $time;
        }

        return $objCart;
    }
}
