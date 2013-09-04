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

namespace Isotope\Model;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Model\Config;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\Shipping;

/**
 * Class ProductCollection
 *
 * Provide methods to handle Isotope product collections.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
abstract class ProductCollection extends TypeAgent
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection';

    /**
     * Interface to validate product collection
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeProductCollection';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Define if data should be threaded as "locked", eg. not apply discount rules to product prices
     * @var boolean
     */
    protected $blnLocked = false;

    /**
     * Cache product items in this collection
     * @var array
     */
    protected $arrItems;

    /**
     * Cache surcharges in this collection
     * @var array
     */
    protected $arrSurcharges;

    /**
     * Errors
     * @var array
     */
    protected $arrErrors = array();

    /**
     * Shipping method for this collection, if shipping is required
     * @var IsotopeShipping
     */
    protected $objShipping = false;

    /**
     * Payment method for this collection, if payment is required
     * @var IsotopePayment
     */
    protected $objPayment = false;

    /**
     * Configuration
     * @var array
     */
    protected $arrSettings = array();

    /**
     * Record has been modified
     * @var boolean
     */
    protected $blnModified = false;


    /**
     * Initialize the object
     */
    public function __construct(\Database\Result $objResult=null)
    {
        parent::__construct($objResult);

        if ($objResult !== null) {
            $this->arrSettings = deserialize($this->arrData['settings'], true);
        }

        // Do not use __destruct, because Database object might be destructed first (see http://github.com/contao/core/issues/2236)
        register_shutdown_function(array($this, 'saveDatabase'));
    }


    /**
     * Shutdown function to save data if modified
     */
    public function saveDatabase()
    {
        if (!$this->blnLocked) {
            $this->save();
        }
    }


    /**
     * Return data
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        // If there is a database field for that key, retrive from there
        if (array_key_exists($strKey, $this->arrData)) {

            return deserialize($this->arrData[$strKey]);
        }

        // Everything else is in arrSettings and serialized
        else {

            return deserialize($this->arrSettings[$strKey]);
        }
    }


    /**
     * Set data
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        // If there is a database field for that key, we store it there
        if (array_key_exists($strKey, $this->arrData) || \Database::getInstance()->fieldExists($strKey, static::$strTable)) {
            $this->arrData[$strKey] = $varValue;
        }

        // Everything else goes into arrSettings and is serialized
        else {
            if ($varValue === null) {
                unset($this->arrSettings[$strKey]);
            } else {
                $this->arrSettings[$strKey] = $varValue;
            }
        }

        // Empty all caches
        $this->setModified(true);
    }

    /**
     * Check whether a property is set
     * @param string
     * @return boolean
     */
    public function __isset($strKey)
    {
        if (isset($this->arrData[$strKey]) || isset($this->arrSettings[$strKey])) {

            return true;
        }

        return false;
    }

    /**
     * Return true if collection is locked
     * @return bool
     */
    public function isLocked()
    {
        return $this->blnLocked;
    }

    /**
     * Return true if collection has no items
     * @return bool
     */
    public function isEmpty()
    {
        $arrItems = $this->getItems();

        return empty($arrItems);
    }

    /**
     * Return true if collection has been modified
     * @return bool
     */
    public function isModified()
    {
        return $this->blnModified;
    }

    /**
     * Mark collection as modified
     * @param bool
     */
    protected function setModified($varValue)
    {
        $this->blnModified = (bool) $varValue;
        $this->arrItems = null;
        $this->arrSurcharges = null;
        $this->arrCache = array();
        $this->arrRelated = array();
    }

    /**
     * Return payment method for this collection
     * @return IsotopePayment|null
     */
    public function getPaymentMethod()
    {
        if (false === $this->objPayment) {
            $this->objPayment = $this->getRelated('payment_id');
        }

        return $this->objPayment;
    }

    /**
     * Set payment method for this collection
     * @param IsotopePayment|null
     */
    public function setPaymentMethod(IsotopePayment $objPayment)
    {
        $this->objPayment = $objPayment;
        $this->payment_id = $objPayment->id;

        $this->setModified(true);
    }

    /**
     * Return surcharge for current payment method
     * @return ProductCollectionSurcharge|null
     */
    public function getPaymentSurcharge()
    {
        return ($this->hasPayment()) ? $this->getPaymentMethod()->getSurcharge($this) : null;
    }

    /**
     * Return boolean wether collection has payment
     * @return bool
     */
    public function hasPayment()
    {
        return (null === $this->getPaymentMethod()) ? false : true;
    }

    /**
     * Return boolean wether collection requires payment
     * @return bool
     */
    public function requiresPayment()
    {
        return $this->getTotal() > 0 ? true : false;
    }

    /**
     * Return shipping method for this collection
     * @return IsotopeShipping|null
     */
    public function getShippingMethod()
    {
        if (false === $this->objShipping) {
            $this->objShipping = $this->getRelated('shipping_id');
        }

        return $this->objShipping;
    }

    /**
     * Set shipping method for this collection
     * @param IsotopeShipping|null
     */
    public function setShippingMethod(IsotopeShipping $objShipping)
    {
        $this->objShipping = $objShipping;
        $this->shipping_id = $objShipping->id;

        $this->setModified(true);
    }

    /**
     * Return surcharge for current shipping method
     * @return ProductCollectionSurcharge|null
     */
    public function getShippingSurcharge()
    {
        return ($this->hasShipping()) ? $this->getShippingMethod()->getSurcharge($this) : null;
    }

    /**
     * Return boolean wether collection has shipping
     * @return bool
     */
    public function hasShipping()
    {
        return (null === $this->getShippingMethod()) ? false : true;
    }

    /**
     * Return boolean wether collection requires shipping
     * @return bool
     */
    public function requiresShipping()
    {
        if (!isset($this->arrCache['requiresShipping'])) {

            $this->arrCache['requiresShipping'] = false;
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {
                if ($objItem->hasProduct() && !$objItem->getProduct()->isExemptFromShipping()) {
                    $this->arrCache['requiresShipping'] = true;
                }
            }
        }

        return $this->arrCache['requiresShipping'];
    }

    /**
     * Get billing address for collection
     * @return  Address|null
     */
    public function getBillingAddress()
    {
        return $this->getRelated('address1_id');
    }

    /**
     * Set billing address for collectino
     * @param   Address
     */
    public function setBillingAddress(Address $objAddress)
    {
        if (null === $objAddress || $objAddress->id < 1) {
            $this->address1_id = 0;
        } else {
            $this->address1_id = $objAddress->id;
        }

        $this->setModified(true);
    }

    /**
     * Get shipping address for collection
     * @return  Address|null
     */
    public function getShippingAddress()
    {
        if (!$this->hasPayment()) {
            return $this->getRelated('address1_id');
        }

        return $this->hasShipping() ? $this->getRelated('address2_id') : null;
    }

    /**
     * Set shipping address for collection
     * @param   Address
     */
    public function setShippingAddress(Address $objAddress)
    {
        if (null === $objAddress || $objAddress->id < 1) {
            $intId = 0;
        } else {
            $intId = $objAddress->id;
        }

        // If the collection does not have a payment, the shipping address is the primary address for the collection
        if (!$this->requiresPayment()) {
            $this->address1_id = $intId;
        } else {
            $this->address2_id = $intId;
        }

        $this->setModified(true);
    }

    /**
     * Return customer email address for the collection
     * @return  string
     */
    public function getEmailRecipient()
    {
        $strName = '';
        $strEmail = '';
        $objBillingAddress = $this->getBillingAddress();
        $objShippingAddress = $this->getShippingAddress();

        if ($objBillingAddress->email != '') {
            $strName = $objBillingAddress->firstname . ' ' . $objBillingAddress->lastname;
            $strEmail = $objBillingAddress->email;
        } elseif ($objShippingAddress->email != '') {
            $strName = $objShippingAddress->firstname . ' ' . $objShippingAddress->lastname;
            $strEmail = $objShippingAddress->email;
        } elseif ($this->member > 0 && ($objMember = \MemberModel::findByPk($this->member)) !== null && $objMember->email != '') {
            $strName = $objMember->firstname . ' ' . $objMember->lastname;
            $strEmail = $objMember->email;
        }

        if (trim($strName) != '') {
            $strEmail = sprintf('"%s" <%s>', \Isotope\Email::romanizeFriendlyName($strName), $strEmail);
        }

        // !HOOK: determine email recipient for collection
        if (isset($GLOBALS['ISO_HOOKS']['emailRecipientForCollection']) && is_array($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $strEmail = $objCallback->$callback[1]($strEmail, $this);
            }
        }

        return $strEmail;
    }

    /**
     * Return number of items in the collection
     * @return  int
     */
    public function countItems()
    {
        if (!isset($this->arrCache['countItems'])) {
            $this->arrCache['countItems'] = ProductCollectionItem::countBy('pid', $this->id);
        }

        return $this->arrCache['countItems'];
    }

    /**
     * Return summary of item quantity in collection
     * @return  int
     */
    public function sumItemsQuantity()
    {
        if (!isset($this->arrCache['sumItemsQuantity'])) {
            $this->arrCache['sumItemsQuantity'] = ProductCollectionItem::countBy('quantity', 'pid', $this->id);
        }

        return $this->arrCache['sumItemsQuantity'];
    }


    /**
     * Load settings from database field
     * @param object
     * @param string
     * @param string
     */
    public function setRow(array $arrData)
    {
        parent::setRow($arrData);
        $this->arrSettings = deserialize($arrData['settings'], true);
    }


    /**
     * Update database with latest product prices and store settings
     * @param   boolean
     * @return  $this
     */
    public function save($blnForceInsert=false)
    {
        if ($this->isLocked()) {
            return $this;
        }

        if ($this->blnModified) {
            $this->arrData['tstamp'] = time();
            $this->arrData['settings'] = serialize($this->arrSettings);
        }

        $arrItems = $this->getItems();

        foreach ($arrItems as $objItem) {

            if (!$objItem->hasProduct() || null === $objItem->getProduct()->getPrice()) {
                continue;
            }

            $objItem->price = $objItem->getProduct()->getPrice()->getAmount($objItem->quantity);
            $objItem->tax_free_price = $objItem->getProduct()->getPrice()->getNetAmount($objItem->quantity);
            $objItem->save();
        }

        // !HOOK: additional functionality when saving a collection
        if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && is_array($GLOBALS['ISO_HOOKS']['saveCollection']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($this);
            }
        }

        if ($this->blnModified || $blnForceInsert) {
            return parent::save($blnForceInsert);
        }

        return $this;
    }


    /**
     * Also delete child table records when dropping this collection
     * @return integer
     */
    public function delete()
    {
        // !HOOK: additional functionality when deleting a collection
        if (isset($GLOBALS['ISO_HOOKS']['deleteCollection']) && is_array($GLOBALS['ISO_HOOKS']['deleteCollection']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['deleteCollection'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $blnRemove = $objCallback->$callback[1]($this);

                if ($blnRemove === false)
                {
                    return 0;
                }
            }
        }

        $intAffectedRows = parent::delete();

        if ($intAffectedRows > 0) {
            \Database::getInstance()->query("DELETE FROM tl_iso_product_collection_item WHERE pid={$this->id}");
            \Database::getInstance()->query("DELETE FROM tl_iso_addresses WHERE ptable='" . static::$strTable . "' AND pid={$this->id}");
        }

        $this->arrCache = array();
        $this->arrItems = null;
        $this->arrSurcharges = null;

        return $intAffectedRows;
    }


    /**
     * Delete all products in the collection
     */
    public function purge()
    {
        $arrItems = $this->getItems();

        foreach ($arrItems as $objItem) {
            $this->deleteItem($objItem);
        }
    }


    /**
     * Lock collection from begin modified
     */
    public function lock()
    {
        if ($this->blnLocked) {
            throw new \LogicException('Product collection is already locked.');
        }

        $this->save();

        $this->blnLocked = true;

        $this->Database->query("UPDATE tl_iso_collection SET locked='1' WHERE id=" . $this->{$this->strPk});
    }


    public function getSubtotal()
    {
        if (!isset($this->arrCache['subtotal'])) {

            $fltAmount = 0;
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {

                $varPrice = $objItem->getPrice() * $objItem->quantity;

                if ($varPrice !== null) {
                    $fltAmount += $varPrice;
                }
            }

            $this->arrCache['subtotal'] = $fltAmount;
        }

        return $this->arrCache['subtotal'];
    }


    public function getTaxFreeSubtotal()
    {
        if (!isset($this->arrCache['taxFreeSubtotal'])) {

            $fltAmount = 0;
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {

                $varPrice = $objItem->getTaxFreePrice() * $objItem->quantity;

                if ($varPrice !== null) {
                    $fltAmount += $varPrice;
                }
            }

            $this->arrCache['taxFreeSubtotal'] = $fltAmount;
        }

        return $this->arrCache['taxFreeSubtotal'];
    }


    public function getTotal()
    {
        if (!isset($this->arrCache['total'])) {

            $fltAmount = $this->getSubtotal();
            $arrSurcharges = $this->getSurcharges();

            foreach ($arrSurcharges as $objSurcharge) {
                if ($objSurcharge->add !== false) {
                    $fltAmount += $objSurcharge->total_price;
                }
            }

            $this->arrCache['total'] = $fltAmount > 0 ? Isotope::roundPrice($fltAmount) : 0;
        }

        return $this->arrCache['total'];
    }


    public function getTaxFreeTotal()
    {
        if (!isset($this->arrCache['taxFreeTotal'])) {

            $fltAmount = $this->getTaxFreeSubtotal();
            $arrSurcharges = $this->getSurcharges();

            foreach ($arrSurcharges as $objSurcharge) {
                if ($objSurcharge->add !== false) {
                    $fltAmount += $objSurcharge->tax_free_total_price;
                }
            }

            $this->arrCache['taxFreeTotal'] = $fltAmount > 0 ? Isotope::roundPrice($fltAmount) : 0;
        }

        return $this->arrCache['taxFreeTotal'];
    }


    /**
     * Return the item with the latest timestamp (e.g. the latest added item)
     * @return ProductCollectionItem|null
     */
    public function getLatestItem()
    {
        if (!isset($this->arrCache['latestItem'])) {

            $latest = 0;
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {
                if ($objItem->tstamp > $latest) {
                    $this->arrCache['latestItem'] = $objItem;
                    $latest = $objItem->tstamp;
                }
            }
        }

        return $this->arrCache['latestItem'];
    }


    /**
     * Return all items in the collection
     * @param  bool
     * @return array
     */
    public function getItems($blnNoCache=false)
    {
        if (null === $this->arrItems || $blnNoCache) {
            $this->arrItems = array();

            if (($objItems = ProductCollectionItem::findBy('pid', $this->id, array('uncached'=>true))) !== null) {
                while ($objItems->next()) {

                    $objItem = $objItems->current();

                    if ($this->isLocked()) {
                        $objItem->lock();
                    }

                    // Add error message for items no longer available
                    if (!$this->isLocked() && (!$objItem->hasProduct() || !$objItem->getProduct()->isAvailableForCollection($this))) {
                        $objItem->addError($GLOBALS['TL_LANG']['ERR']['collectionItemNotAvailable']);
                    }

                    $this->arrItems[$objItem->id] = $objItem;
                }
            }
        }

        return $this->arrItems;
    }


    /**
     * Search item for a specific product
     * @param  IsotopeProduct
     * @return ProductCollectionItem|null
     */
    public function getItemForProduct(IsotopeProduct $objProduct)
    {
        $strClass = $objProduct->getRelated('type')->class;

        $objItem = ProductCollectionItem::findBy(array('pid=?', 'type=?', 'product_id=?', 'options=?'), array($this->id, $strClass, $objProduct->id, serialize($objProduct->getOptions())));

        return (null === $objItem) ? null : $this->arrItems[$objItem->id];
    }


    /**
     * Check if a given product is already in the collection
     * @param  IsotopeProduct
     * @param  bool
     * @return bool
     */
    public function hasProduct(IsotopeProduct $objProduct, $blnIdentical=true)
    {
        if (true === $blnIdentical) {

            $objItem = $this->getItemForProduct($objProduct);

            return (null === $objItem) ? false : true;

        } else {

            $intId = $objProduct->pid ?: $objProduct->id;

            foreach ($this->getItems() as $objItem) {

                if ($objItem->getProduct()->id == $intId || $objItem->getProduct()->pid == $intId) {
                    return true;
                }
            }

            return false;
        }
    }


    /**
     * Add a product to the collection
     * @param   object The product object
     * @param   integer How many products to add
     * @return  ProductCollectionItem
     */
    public function addProduct(IsotopeProduct $objProduct, $intQuantity)
    {
        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['addProductToCollection']) && is_array($GLOBALS['ISO_HOOKS']['addProductToCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['addProductToCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $intQuantity = $objCallback->$callback[1]($objProduct, $intQuantity, $this);
            }
        }

        if ($intQuantity == 0) {
            return false;
        }

        $time = time();
        $this->setModified(true);

        // Make sure collection is in DB before adding product
        if (!isset($this->{static::$strPk})) {
            $this->save();
        }

        // Remove uploaded files from session so they are not added to the next product (see #646)
        unset($_SESSION['FILES']);

        $objItem = $this->getItemForProduct($objProduct);
        $intMinimumQuantity = $objProduct->getMinimumQuantity();

        if (null !== $objItem)
        {
            if (($objItem->quantity + $intQuantity) < $intMinimumQuantity) {
                $_SESSION['ISO_INFO'][] = sprintf($GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'], $objProduct->name, $intMinimumQuantity);
                $intQuantity = $intMinimumQuantity - $objItem->quantity;
            }

            $objItem->increaseQuantityBy($intQuantity);

            return $objItem;
        }
        else
        {
            if ($intQuantity < $intMinimumQuantity) {
                $_SESSION['ISO_INFO'][] = sprintf($GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'], $objProduct->name, $intMinimumQuantity);
                $intQuantity = $intMinimumQuantity;
            }

            $objItem = new ProductCollectionItem();
            $objItem->pid               = $this->id;
            $objItem->tstamp            = $time;
            $objItem->type              = $objProduct->getRelated('type')->class;
            $objItem->product_id        = (int) $objProduct->id;
            $objItem->sku               = (string) $objProduct->sku;
            $objItem->name              = (string) $objProduct->name;
            $objItem->options           = $objProduct->getOptions();
            $objItem->quantity          = (int) $intQuantity;
            $objItem->price             = (float) ($objProduct->getPrice() ? $objProduct->getPrice()->getAmount((int) $intQuantity) : 0);
            $objItem->tax_free_price    = (float) ($objProduct->getPrice() ? $objProduct->getPrice()->getNetAmount((int) $intQuantity) : 0);
            $objItem->href_reader       = $objProduct->href_reader;

            $objItem->save();

            // Add the new item to our cache
            $this->arrItems[$objItem->id] = $objItem;

            return $objItem;
        }
    }


    /**
     * Update a product collection item
     * @param   object  The product object
     * @param   array   The property(ies) to adjust
     * @return  bool
     */
    public function updateItem(ProductCollectionItem $objItem, $arrSet)
    {
        return $this->updateItemById($objItem->id, $arrSet);
    }

    /**
     * Update product collection item with given ID
     * @param   int
     * @param   array
     * @return  bool
     */
    public function updateItemById($intId, $arrSet)
    {
        $arrItems = $this->getItems();

        if (!isset($arrItems[$intId])) {
            return false;
        }

        $objItem = $arrItems[$intId];

        // !HOOK: additional functionality when updating a product in the collection
        if (isset($GLOBALS['ISO_HOOKS']['updateItemInCollection']) && is_array($GLOBALS['ISO_HOOKS']['updateItemInCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['updateItemInCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrSet = $objCallback->$callback[1]($objItem, $arrSet, $this);

                if (empty($arrSet) && is_array($arrSet)) {
                    return false;
                }
            }
        }

        // Quantity set to 0, delete item
        if (isset($arrSet['quantity']) && $arrSet['quantity'] == 0) {
            return $this->deleteItemById($intId);
        }

        if (isset($arrSet['quantity']) && $objItem->hasProduct()) {

            // Set product quantity so we can determine the correct minimum price
            $objProduct = $objItem->getProduct();
            $intMinimumQuantity = $objProduct->getMinimumQuantity();

            if ($arrSet['quantity'] < $intMinimumQuantity) {
                $_SESSION['ISO_INFO'][] = sprintf($GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'], $objProduct->name, $intMinimumQuantity);
                $arrSet['quantity'] = $intMinimumQuantity;
            }
        }

        $arrSet['tstamp'] = time();

        foreach ($arrSet as $k => $v) {
            $objItem->$k = $v;
        }

        $objItem->save();
        $this->setModified(true);

        return true;
    }


    /**
     * Remove item from collection
     * @param   ProductCollectionItem
     * @return  bool
     */
    public function deleteItem(ProductCollectionItem $objItem)
    {
        return $this->deleteItemById($objItem->id);
    }

    /**
     * Remove item with given ID from collection
     * @param   int
     * @return  bool
     */
    public function deleteItemById($intId)
    {
        $arrItems = $this->getItems();

        if (!isset($arrItems[$intId])) {
            return false;
        }

        // !HOOK: additional functionality when a product is removed from the collection
        if (isset($GLOBALS['ISO_HOOKS']['deleteItemFromCollection']) && is_array($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $blnRemove = $objCallback->$callback[1]($arrItems[$intId], $this);

                if ($blnRemove === false) {
                    return false;
                }
            }
        }

        $arrItems[$intId]->delete();
        $this->setModified(true);

        return true;
    }


    public function getSurcharges()
    {
        if (null === $this->arrSurcharges) {
            $this->arrSurcharges = $this->isLocked() ? ProductCollectionSurcharge::findBy('pid', $this->id) : ProductCollectionSurcharge::findForCollection($this);
        }

        return $this->arrSurcharges;
    }


    /**
     * Initialize a new collection from given collection
     * @param   IsotopeProductCollection
     * @return  IsotopeProductCollection
     */
    public function setSourceCollection(IsotopeProductCollection $objSource)
    {
        global $objPage;

        $objConfig = Config::findByPk($objSource->config_id);

        if (null === $objConfig) {
            $objConfig = Isotope::getConfig();
        }

        // Store in arrData, otherwise each call to __set would trigger setModified(true)
        $this->arrData['source_collection_id'] = $objSource->id;
        $this->arrData['config_id']            = $objSource->config_id;
        $this->arrData['store_id']             = $objConfig->store_id;
        $this->arrData['member']               = $objSource->member;
        $this->arrData['language']             = $GLOBALS['TL_LANGUAGE'];
        $this->arrData['currency']             = $objConfig->currency;

        $this->pageId                           = (int) $objPage->id;

        // Do not change the unique ID
        if ($this->arrData['uniqid'] == '') {
            $this->arrData['uniqid'] = uniqid(Isotope::getInstance()->call('replaceInsertTags', $objConfig->orderPrefix), true);
        }

        $this->setModified(true);
    }


    /**
     * Copy product collection items from another collection to this one (e.g. Cart to Order)
     * @param object
     * @param boolean
     * @return array
     */
    public function copyItemsFrom(IsotopeProductCollection $objSource)
    {
        $this->save();

        // Make sure database table has the latest prices
        $objSource->save();

        $time = time();
        $arrIds = array();
        $arrOldItems = $objSource->getItems();

        foreach ($arrOldItems as $objOldItem) {

            // !HOOK: additional functionality when copying product to collection
            if (isset($GLOBALS['ISO_HOOKS']['copyCollectionItem']) && is_array($GLOBALS['ISO_HOOKS']['copyCollectionItem'])) {
                foreach ($GLOBALS['ISO_HOOKS']['copyCollectionItem'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);

                    if ($objCallback->$callback[1]($objOldItem, $objSource, $this) === false) {
                        continue;
                    }
                }
            }

            if ($objOldItem->hasProduct() && $this->hasProduct($objOldItem->getProduct())) {

                $objNewItem = $this->getItemForProduct($objOldItem->getProduct());
                $objNewItem->increaseQuantityBy($objOldItem->quantity);

            } else {

                $objNewItem = clone $objOldItem;
                $objNewItem->pid = $this->id;
                $objNewItem->tstamp = $time;
                $objNewItem->save(true);
            }

            $arrIds[$objOldItem->id] = $objNewItem->id;
        }

        if (!empty($arrIds)) {
            $this->setModified(true);
        }

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['copiedCollectionItems']) && is_array($GLOBALS['ISO_HOOKS']['copiedCollectionItems'])) {
            foreach ($GLOBALS['ISO_HOOKS']['copiedCollectionItems'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($objSource, $this, $arrIds);
            }
        }

        return $arrIds;
    }


    /**
     * Calculate the weight of all products in the cart in a specific weight unit
     * @param string
     * @return mixed
     */
    public function getShippingWeight($unit)
    {
        $arrWeights = array();
        $arrItems = $this->getItems();

        foreach ($arrItems as $objItem)
        {
            if (!$objItem->hasProduct()) {
                continue;
            }

            $arrWeight = deserialize($objItem->getProduct()->shipping_weight, true);
            $arrWeight['value'] = $objItem->quantity * floatval($arrWeight['value']);

            $arrWeights[] = $arrWeight;
        }

        return Isotope::calculateWeight($arrWeights, $unit);
    }


    /**
     * Add the collection to a template
     * @param   object
     */
    public function addToTemplate($objTemplate)
    {
        $objModule = $this;
        $arrGalleries = array();
        $arrItems = array();

        foreach ($this->getItems() as $objItem) {

            $blnHasProduct = $objItem->hasProduct();
            $objProduct = $objItem->getProduct();

            // Set the active product for insert tags replacement
            $GLOBALS['ACTIVE_PRODUCT'] = $objProduct;

            $arrItems[] = array(
                'id'                => $objItem->id,
                'sku'               => $objItem->getSku(),
                'name'              => $objItem->getName(),
                'options'           => Isotope::formatOptions($objItem->getOptions()),
                'quantity'          => $objItem->quantity,
                'price'             => Isotope::formatPriceWithCurrency($objItem->getPrice()),
                'tax_free_price'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreePrice()),
                'total'             => Isotope::formatPriceWithCurrency($objItem->getTotalPrice()),
                'tax_free_total'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreeTotalPrice() * $objItem->quantity),
                'tax_id'            => $objItem->tax_id,
                'hasProduct'        => $blnHasProduct,
                'product'           => $objProduct,
                'item'              => $objItem,
                'raw'               => $objItem->row(),
                'rowClass'          => trim('product ' . (($blnHasProduct && $objProduct->isNew()) ? 'new ' : '') . $objProduct->cssID[1]),
            );

            unset($GLOBALS['ACTIVE_PRODUCT']);
        }

        $objTemplate->collection = $this;
        $objTemplate->config = ($this->getRelated('config_id') || Isotope::getConfig());
        $objTemplate->items = \Isotope\Frontend::generateRowClass($arrItems, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
        $objTemplate->surcharges = \Isotope\Frontend::formatSurcharges($this->getSurcharges());
        $objTemplate->subtotal = Isotope::formatPriceWithCurrency($this->getSubtotal());
        $objTemplate->total = Isotope::formatPriceWithCurrency($this->getTotal());

        $objTemplate->generateAttribute = function($strAttribute, $objItem) {

            if (!$objItem->hasProduct()) {
                return '';
            }

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_products']['attributes'][$strAttribute];

            if (!($objAttribute instanceof IsotopeAttribute)) {
                throw new \InvalidArgumentException($strAttribute . ' is not a valid attribute');
            }

            return $objAttribute->generate($objItem->getProduct());
        };

        $objTemplate->getGallery = function($strAttribute, $objItem) use ($objModule, &$arrGalleries) {

            if (!$objItem->hasProduct()) {
                return new \Isotope\Model\Gallery\Standard();
            }

            $strCacheKey = 'product' . $objItem->product_id . '_' . $strAttribute;

            if (!isset($arrGalleries[$strCacheKey])) {
                $arrGalleries[$strCacheKey] = Gallery::createForProductAttribute($objModule->gallery, $objItem->getProduct(), $strAttribute);
            }

            return $arrGalleries[$strCacheKey];
        };

        // !HOOK: allow overriding of the template
        if (isset($GLOBALS['ISO_HOOKS']['generateCollection']) && is_array($GLOBALS['ISO_HOOKS']['generateCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($objTemplate, $arrItems, $this);
            }
        }
    }

    /**
     * Add an error message
     * @param   string
     */
    public function addError($strError)
    {
        $this->arrErrors[] = $strError;
    }

    /**
     * Check if collection or any item has errors
     * @return  bool
     */
    public function hasErrors()
    {
        if (!empty($this->arrErrors)) {
            return true;
        }

        foreach ($this->getItems() as $objItem) {
            if ($objItem->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the errors array
     * @return  array
     */
    public function getErrors()
    {
        $arrErrors = $this->arrErrors;

        foreach ($this->getItems() as $objItem) {
            if ($objItem->hasErrors()) {
                array_unshift($arrErrors, $this->getMessageIfErrorsInItems());
                break;
            }
        }

        return $arrErrors;
    }

    /**
     * Get a collection-specific error message for items with errors
     * @return  string
     */
    protected function getMessageIfErrorsInItems()
    {
        return $GLOBALS['TL_LANG']['ERR']['collectionErrorInItems'];
    }
}
