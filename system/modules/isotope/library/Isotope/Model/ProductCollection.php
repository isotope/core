<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Haste\Generator\RowClass;
use Haste\Haste;
use Haste\Units\Mass\Scale;
use Haste\Units\Mass\Weighable;
use Haste\Units\Mass\WeightAggregate;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Model\Payment;
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
     * Cache
     * @var array
     */
    protected $arrCache;

    /**
     * Cache product items in this collection
     * @var ProductCollectionItem[]
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
     * Constructor
     *
     * @param \Database\Result $objResult
     */
    public function __construct(\Database\Result $objResult = null)
    {
        parent::__construct($objResult);

        $this->arrData['uniqid'] = $this->generateUniqueId();

        // Do not use __destruct, because Database object might be destructed first (see http://github.com/contao/core/issues/2236)
        if (TL_MODE == 'FE') {
            register_shutdown_function(array($this, 'updateDatabase'), false);
        }
    }

    /**
     * Prevent cloning because we can't copy items etc.
     */
    public function __clone()
    {
        throw new \LogicException('Product collections can\'t be cloned, you should probably use ProductCollection::createFromCollection');
    }

    /**
     * Shutdown function to update prices of items and collection
     *
     * @param boolean $blnCreate If true create Model even if not in registry or not saved at all
     */
    public function updateDatabase($blnCreate = true)
    {
        if (!$this->isLocked()
            && !$this->blnPreventSaving
            && (\Model\Registry::getInstance()->isRegistered($this) || $blnCreate)
        ) {

            foreach ($this->getItems() as $objItem) {
                if (!$objItem->hasProduct()) {
                    continue;
                }

                $objItem->price          = $objItem->getPrice();
                $objItem->tax_free_price = $objItem->getTaxFreePrice();
                $objItem->save();
            }

            // First call to __set for tstamp will truncate the cache
            $this->tstamp            = time();
            $this->subtotal          = $this->getSubtotal();
            $this->tax_free_subtotal = $this->getTaxFreeSubtotal();
            $this->total             = $this->getTotal();
            $this->tax_free_total    = $this->getTaxFreeTotal();
            $this->currency          = (string) $this->getRelated('config_id')->currency;

            $this->save();
        }
    }

    /**
     * Mark a field as modified
     *
     * @param string $strKey The field key
     */
    public function markModified($strKey)
    {
        if ($strKey == 'locked') {
            throw new \InvalidArgumentException('Cannot change lock status of collection');
        }

        if ($strKey == 'document_number') {
            throw new \InvalidArgumentException('Cannot change document number of a collection, must be generated using generateDocumentNumber()');
        }

        $this->clearCache();

        parent::markModified($strKey);
    }

    /**
     * Return true if collection is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return (isset($this->locked) && $this->locked !== '');
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
     * Return payment method for this collection
     *
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
     *
     * @param IsotopePayment $objPayment
     */
    public function setPaymentMethod(IsotopePayment $objPayment = null)
    {
        $this->payment_id = (null === $objPayment ? 0 : $objPayment->id);
        $this->objPayment = $objPayment;
    }

    /**
     * Return surcharge for current payment method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getPaymentSurcharge()
    {
        return ($this->hasPayment()) ? $this->getPaymentMethod()->getSurcharge($this) : null;
    }

    /**
     * Return boolean whether collection has payment
     *
     * @return bool
     */
    public function hasPayment()
    {
        return (null === $this->getPaymentMethod()) ? false : true;
    }

    /**
     * Return boolean whether collection requires payment
     *
     * @return bool
     */
    public function requiresPayment()
    {
        return $this->getTotal() > 0 ? true : false;
    }

    /**
     * Return shipping method for this collection
     *
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
     *
     * @param IsotopeShipping $objShipping
     */
    public function setShippingMethod(IsotopeShipping $objShipping = null)
    {
        $this->shipping_id = (null === $objShipping ? 0 : $objShipping->id);
        $this->objShipping = $objShipping;
    }

    /**
     * Return surcharge for current shipping method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getShippingSurcharge()
    {
        return ($this->hasShipping()) ? $this->getShippingMethod()->getSurcharge($this) : null;
    }

    /**
     * Return boolean whether collection has shipping
     *
     * @return bool
     */
    public function hasShipping()
    {
        return (null === $this->getShippingMethod()) ? false : true;
    }

    /**
     * Return boolean whether collection requires shipping
     *
     * @return bool
     */
    public function requiresShipping()
    {
        if (!isset($this->arrCache['requiresShipping'])) {

            $this->arrCache['requiresShipping'] = false;
            $arrItems                           = $this->getItems();

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
     *
     * @return  \Isotope\Model\Address|null
     */
    public function getBillingAddress()
    {
        return $this->getRelated('billing_address_id');
    }

    /**
     * Set billing address for collection
     *
     * @param Address $objAddress
     */
    public function setBillingAddress(Address $objAddress = null)
    {
        if (null === $objAddress || $objAddress->id < 1) {
            $this->billing_address_id = 0;
        } else {
            $this->billing_address_id = $objAddress->id;
        }
    }

    /**
     * Get shipping address for collection
     *
     * @return  Address|null
     */
    public function getShippingAddress()
    {
        return $this->requiresShipping() ? $this->getRelated('shipping_address_id') : null;
    }

    /**
     * Set shipping address for collection
     *
     * @param Address $objAddress
     */
    public function setShippingAddress(Address $objAddress = null)
    {
        if (null === $objAddress || $objAddress->id < 1) {
            $this->shipping_address_id = 0;
        } else {
            $this->shipping_address_id = $objAddress->id;
        }
    }

    /**
     * Return customer email address for the collection
     *
     * @return string
     */
    public function getEmailRecipient()
    {
        $strName            = '';
        $strEmail           = '';
        $objBillingAddress  = $this->getBillingAddress();
        $objShippingAddress = $this->getShippingAddress();

        if ($objBillingAddress->email != '') {
            $strName  = $objBillingAddress->firstname . ' ' . $objBillingAddress->lastname;
            $strEmail = $objBillingAddress->email;
        } elseif ($objShippingAddress->email != '') {
            $strName  = $objShippingAddress->firstname . ' ' . $objShippingAddress->lastname;
            $strEmail = $objShippingAddress->email;
        } elseif ($this->member > 0 && ($objMember = \MemberModel::findByPk($this->member)) !== null && $objMember->email != '') {
            $strName  = $objMember->firstname . ' ' . $objMember->lastname;
            $strEmail = $objMember->email;
        }

        if (trim($strName) != '') {

            // Romanize friendly name to prevent email issues
            $strName = html_entity_decode($strName, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
            $strName = strip_insert_tags($strName);
            $strName = utf8_romanize($strName);
            $strName = preg_replace('/[^A-Za-z0-9\.!#$%&\'*+-\/=?^_ `{\|}~]+/i', '_', $strName);

            $strEmail = sprintf('"%s" <%s>', $strName, $strEmail);
        }

        // !HOOK: determine email recipient for collection
        if (isset($GLOBALS['ISO_HOOKS']['emailRecipientForCollection']) && is_array($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $strEmail    = $objCallback->$callback[1]($strEmail, $this);
            }
        }

        return $strEmail;
    }

    /**
     * Return number of items in the collection
     *
     * @return int
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
     *
     * @return int
     */
    public function sumItemsQuantity()
    {
        if (!isset($this->arrCache['sumItemsQuantity'])) {
            $this->arrCache['sumItemsQuantity'] = ProductCollectionItem::sumBy('quantity', 'pid', $this->id);
        }

        return $this->arrCache['sumItemsQuantity'];
    }

    /**
     * Load settings from database field
     *
     * @param array $arrData
     *
     * @return $this
     */
    public function setRow(array $arrData)
    {
        parent::setRow($arrData);

        // Merge settings into arrData, save() will move the values back
        $this->arrData = array_merge(deserialize($arrData['settings'], true), $this->arrData);

        return $this;
    }

    /**
     * Save all non-database fields in the settings array
     *
     * @return $this
     */
    public function save()
    {
        // The instance cannot be saved
        if ($this->blnPreventSaving) {
            throw new \LogicException('The model instance has been detached and cannot be saved');
        }

        // !HOOK: additional functionality when saving a collection
        if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && is_array($GLOBALS['ISO_HOOKS']['saveCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($this);
            }
        }

        $arrDbFields = \Database::getInstance()->getFieldNames(static::$strTable);
        $arrModified = array_diff_key($this->arrModified, array_flip($arrDbFields));

        if (!empty($arrModified)) {
            $arrSettings = deserialize($this->settings, true);
            $arrSettings = array_merge($arrSettings, array_intersect_key($this->arrData, $arrModified));

            $this->settings = serialize($arrSettings);
        }

        return parent::save();
    }

    /**
     * Also delete child table records when dropping this collection
     *
     * @param bool $blnForce Force to delete the collection even if it's locked
     *
     * @return int Number of rows affected
     */
    public function delete($blnForce = false)
    {
        if (!$blnForce) {
            $this->ensureNotLocked();

            // !HOOK: additional functionality when deleting a collection
            if (isset($GLOBALS['ISO_HOOKS']['deleteCollection']) && is_array($GLOBALS['ISO_HOOKS']['deleteCollection'])) {
                foreach ($GLOBALS['ISO_HOOKS']['deleteCollection'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);
                    $blnRemove = $objCallback->$callback[1]($this);

                    if ($blnRemove === false) {
                        return 0;
                    }
                }
            }
        }

        $intPid          = $this->id;
        $intAffectedRows = parent::delete();

        if ($intAffectedRows > 0 && $intPid > 0) {
            \Database::getInstance()->query("DELETE FROM " . ProductCollectionDownload::getTable() . " WHERE pid IN (SELECT id FROM " . ProductCollectionItem::getTable() . " WHERE pid=$intPid)");
            \Database::getInstance()->query("DELETE FROM " . ProductCollectionItem::getTable() . " WHERE pid=$intPid");
            \Database::getInstance()->query("DELETE FROM " . ProductCollectionSurcharge::getTable() . " WHERE pid=$intPid");
            \Database::getInstance()->query("DELETE FROM " . Address::getTable() . " WHERE ptable='" . static::$strTable . "' AND pid=$intPid");
        }

        $this->arrCache      = array();
        $this->arrItems      = null;
        $this->arrSurcharges = null;

        return $intAffectedRows;
    }

    /**
     * Delete all products in the collection
     */
    public function purge()
    {
        $this->ensureNotLocked();

        foreach ($this->getItems() as $objItem) {
            $this->deleteItem($objItem);
        }

        foreach ($this->getSurcharges() as $objSurcharge) {
            if ($objSurcharge->id) {
                $objSurcharge->delete();
            }
        }

        $this->clearCache();
    }

    /**
     * Lock collection from begin modified
     */
    public function lock()
    {
        $this->ensureNotLocked();

        global $objPage;
        $time = time();

        $this->pageId = (int) $objPage->id;
        $this->language = (string) $GLOBALS['TL_LANGUAGE'];

        $this->updateDatabase();
        $this->createPrivateAddresses();

        // Add surcharges to the collection
        $sorting = 128;
        foreach ($this->getSurcharges() as $objSurcharge) {
            $objSurcharge->pid     = $this->id;
            $objSurcharge->tstamp  = $time;
            $objSurcharge->sorting = $sorting;
            $objSurcharge->save();

            $sorting += 128;
        }

        // Add downloads from products to the collection
        foreach (ProductCollectionDownload::createForProductsInCollection($this) as $objDownload) {
            $objDownload->save();
        }

        // Can't use model, it would not save as soon as it's locked
        \Database::getInstance()->query("UPDATE " . static::$strTable . " SET locked=" . $time . " WHERE id=" . $this->id);
        $this->arrData['locked'] = $time;

        // !HOOK: pre-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['collectionLocked']) && is_array($GLOBALS['ISO_HOOKS']['collectionLocked'])) {
            foreach ($GLOBALS['ISO_HOOKS']['collectionLocked'] as $callback) {
                \System::importStatic($callback[0])->{$callback[1]}($this);
            }
        }

        $this->clearCache();
    }

    /**
     * Sum total price of all items in the collection
     *
     * @return float
     */
    public function getSubtotal()
    {
        if ($this->isLocked()) {
            return $this->subtotal;
        }

        if (!isset($this->arrCache['subtotal'])) {

            $fltAmount = 0;
            $arrItems  = $this->getItems();

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

    /**
     * Sum total tax free price of all items in the collection
     *
     * @return float
     */
    public function getTaxFreeSubtotal()
    {
        if ($this->isLocked()) {
            return $this->tax_free_subtotal;
        }

        if (!isset($this->arrCache['taxFreeSubtotal'])) {

            $fltAmount = 0;
            $arrItems  = $this->getItems();

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

    /**
     * Sum total price of items and surcharges
     *
     * @return float
     */
    public function getTotal()
    {
        if ($this->isLocked()) {
            return $this->total;
        }

        if (!isset($this->arrCache['total'])) {

            $fltAmount     = $this->getSubtotal();
            $arrSurcharges = $this->getSurcharges();

            foreach ($arrSurcharges as $objSurcharge) {
                if ($objSurcharge->addToTotal) {
                    $fltAmount += $objSurcharge->total_price;
                }
            }

            $this->arrCache['total'] = $fltAmount > 0 ? Isotope::roundPrice($fltAmount) : 0;
        }

        return $this->arrCache['total'];
    }

    /**
     * Sum tax free total of items and surcharges
     *
     * @return float
     */
    public function getTaxFreeTotal()
    {
        if ($this->isLocked()) {
            return $this->tax_free_total;
        }

        if (!isset($this->arrCache['taxFreeTotal'])) {

            $fltAmount     = $this->getTaxFreeSubtotal();
            $arrSurcharges = $this->getSurcharges();

            foreach ($arrSurcharges as $objSurcharge) {
                if ($objSurcharge->addToTotal) {
                    $fltAmount += $objSurcharge->tax_free_total_price;
                }
            }

            $this->arrCache['taxFreeTotal'] = $fltAmount > 0 ? Isotope::roundPrice($fltAmount) : 0;
        }

        return $this->arrCache['taxFreeTotal'];
    }


    /**
     * Return the item with the latest timestamp (e.g. the latest added item)
     *
     * @return ProductCollectionItem|null
     */
    public function getLatestItem()
    {
        if (!isset($this->arrCache['latestItem'])) {

            $latest   = 0;
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {
                if ($objItem->tstamp > $latest) {
                    $this->arrCache['latestItem'] = $objItem;
                    $latest                       = $objItem->tstamp;
                }
            }
        }

        return $this->arrCache['latestItem'];
    }

    /**
     * Return timestamp when this collection was created
     * This is relevant for price calculation
     *
     * @return int
     */
    public function getLastModification()
    {
        if ($this->isLocked()) {
            return $this->locked;
        }

        return $this->tstamp ? : time();
    }

    /**
     * Return all items in the collection
     *
     * @param callable $varCallable
     * @param bool     $blnNoCache
     *
     * @return ProductCollectionItem[]
     */
    public function getItems($varCallable = null, $blnNoCache = false)
    {
        if (null === $this->arrItems || $blnNoCache) {
            $this->arrItems = array();

            if (($objItems = ProductCollectionItem::findBy('pid', $this->id)) !== null) {

                /** @var ProductCollectionItem $objItem */
                foreach ($objItems as $objItem) {

                    if ($this->isLocked()) {
                        $objItem->lock();
                    }

                    // Add error message for items no longer available
                    if (!$objItem->isAvailable()) {
                        $objItem->addError($GLOBALS['TL_LANG']['ERR']['collectionItemNotAvailable']);
                    }

                    $this->arrItems[$objItem->id] = $objItem;
                }
            }
        }

        if ($varCallable === null) {
            return $this->arrItems;
        }

        // not allowed to chance items
        $arrItems = $this->arrItems;

        return call_user_func($varCallable, $arrItems);
    }


    /**
     * Search item for a specific product
     *
     * @param IsotopeProduct $objProduct
     *
     * @return ProductCollectionItem|null
     */
    public function getItemForProduct(IsotopeProduct $objProduct)
    {
        $strClass = array_search(get_class($objProduct), Product::getModelTypes());

        $objItem = ProductCollectionItem::findOneBy(array('pid=?', 'type=?', 'product_id=?', 'options=?'), array($this->id, $strClass, $objProduct->{$objProduct->getPk()}, serialize($objProduct->getOptions())));

        return $objItem;
    }


    /**
     * Check if a given product is already in the collection
     *
     * @param IsotopeProduct $objProduct
     * @param bool           $blnIdentical
     *
     * @return bool
     */
    public function hasProduct(IsotopeProduct $objProduct, $blnIdentical = true)
    {
        if (true === $blnIdentical) {

            $objItem = $this->getItemForProduct($objProduct);

            return (null === $objItem) ? false : true;

        } else {

            $intId = $objProduct->pid ? : $objProduct->id;

            foreach ($this->getItems() as $objItem) {

                if ($objItem->hasProduct() && ($objItem->getProduct()->id == $intId || $objItem->getProduct()->pid == $intId)) {
                    return true;
                }
            }

            return false;
        }
    }


    /**
     * Add a product to the collection
     *
     * @param IsotopeProduct $objProduct
     * @param int            $intQuantity
     * @param array          $arrConfig
     *
     * @return ProductCollectionItem
     */
    public function addProduct(IsotopeProduct $objProduct, $intQuantity, array $arrConfig = array())
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

        $time         = time();
        $this->tstamp = $time;

        // Make sure collection is in DB before adding product
        if (!\Model\Registry::getInstance()->isRegistered($this)) {
            $this->save();
        }

        // Remove uploaded files from session so they are not added to the next product (see #646)
        unset($_SESSION['FILES']);

        $objItem            = $this->getItemForProduct($objProduct);
        $intMinimumQuantity = $objProduct->getMinimumQuantity();

        if (null !== $objItem) {
            if (($objItem->quantity + $intQuantity) < $intMinimumQuantity) {
                $_SESSION['ISO_INFO'][] = sprintf($GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'], $objProduct->name, $intMinimumQuantity);
                $intQuantity            = $intMinimumQuantity - $objItem->quantity;
            }

            $objItem->increaseQuantityBy($intQuantity);

            return $objItem;
        } else {
            if ($intQuantity < $intMinimumQuantity) {
                $_SESSION['ISO_INFO'][] = sprintf($GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'], $objProduct->name, $intMinimumQuantity);
                $intQuantity            = $intMinimumQuantity;
            }

            $objItem                 = new ProductCollectionItem();
            $objItem->pid            = $this->id;
            $objItem->tstamp         = $time;
            $objItem->type           = array_search(get_class($objProduct), Product::getModelTypes());
            $objItem->product_id     = $objProduct->{$objProduct->getPk()};
            $objItem->sku            = (string) $objProduct->sku;
            $objItem->name           = (string) $objProduct->name;
            $objItem->options        = $objProduct->getOptions();
            $objItem->quantity       = (int) $intQuantity;
            $objItem->price          = (float) ($objProduct->getPrice($this) ? $objProduct->getPrice($this)->getAmount((int) $intQuantity) : 0);
            $objItem->tax_free_price = (float) ($objProduct->getPrice($this) ? $objProduct->getPrice($this)->getNetAmount((int) $intQuantity) : 0);
            $objItem->jumpTo         = (int) $arrConfig['jumpTo']->id;

            $objItem->save();

            // Add the new item to our cache
            $this->arrItems[$objItem->id] = $objItem;

            return $objItem;
        }
    }


    /**
     * Update a product collection item
     *
     * @param ProductCollectionItem $objItem The product object
     * @param array                 $arrSet  The property(ies) to adjust
     *
     * @return bool
     */
    public function updateItem(ProductCollectionItem $objItem, $arrSet)
    {
        return $this->updateItemById($objItem->id, $arrSet);
    }

    /**
     * Update product collection item with given ID
     *
     * @param int   $intId
     * @param array $arrSet
     *
     * @return bool
     */
    public function updateItemById($intId, $arrSet)
    {
        $this->ensureNotLocked();

        $arrItems = $this->getItems();

        if (!isset($arrItems[$intId])) {
            return false;
        }

        /** @var ProductCollectionItem $objItem */
        $objItem = $arrItems[$intId];

        // !HOOK: additional functionality when updating a product in the collection
        if (isset($GLOBALS['ISO_HOOKS']['updateItemInCollection']) && is_array($GLOBALS['ISO_HOOKS']['updateItemInCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['updateItemInCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrSet      = $objCallback->$callback[1]($objItem, $arrSet, $this);

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
            $objProduct         = $objItem->getProduct();
            $intMinimumQuantity = $objProduct->getMinimumQuantity();

            if ($arrSet['quantity'] < $intMinimumQuantity) {
                $_SESSION['ISO_INFO'][] = sprintf($GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'], $objProduct->name, $intMinimumQuantity);
                $arrSet['quantity']     = $intMinimumQuantity;
            }
        }

        $arrSet['tstamp'] = time();

        foreach ($arrSet as $k => $v) {
            $objItem->$k = $v;
        }

        $objItem->save();
        $this->tstamp = time();

        return true;
    }


    /**
     * Remove item from collection
     *
     * @param ProductCollectionItem $objItem
     *
     * @return bool
     */
    public function deleteItem(ProductCollectionItem $objItem)
    {
        return $this->deleteItemById($objItem->id);
    }

    /**
     * Remove item with given ID from collection
     *
     * @param int $intId
     *
     * @return bool
     */
    public function deleteItemById($intId)
    {
        $this->ensureNotLocked();

        $arrItems = $this->getItems();

        if (!isset($arrItems[$intId])) {
            return false;
        }

        // !HOOK: additional functionality when a product is removed from the collection
        if (isset($GLOBALS['ISO_HOOKS']['deleteItemFromCollection']) && is_array($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $blnRemove   = $objCallback->$callback[1]($arrItems[$intId], $this);

                if ($blnRemove === false) {
                    return false;
                }
            }
        }

        $arrItems[$intId]->delete();

        unset($this->arrItems[$intId]);

        $this->tstamp = time();

        return true;
    }

    /**
     * Find surcharges for the current collection
     *
     * @return ProductCollectionSurcharge[]
     */
    public function getSurcharges()
    {
        if (null === $this->arrSurcharges) {
            if ($this->isLocked()) {
                $this->arrSurcharges = array();

                if (($objSurcharges = ProductCollectionSurcharge::findBy('pid', $this->id)) !== null) {
                    $this->arrSurcharges = $objSurcharges->getModels();
                }
            } else {
                $this->arrSurcharges = ProductCollectionSurcharge::findForCollection($this);
            }
        }

        return $this->arrSurcharges;
    }

    /**
     * Copy product collection items from another collection to this one (e.g. Cart to Order)
     *
     * @param IsotopeProductCollection $objSource
     *
     * @return int[]
     */
    public function copyItemsFrom(IsotopeProductCollection $objSource)
    {
        $this->ensureNotLocked();

        $this->updateDatabase();

        // Make sure database table has the latest prices
        $objSource->updateDatabase();

        $time        = time();
        $arrIds      = array();
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

                $objNewItem         = clone $objOldItem;
                $objNewItem->pid    = $this->id;
                $objNewItem->tstamp = $time;
                $objNewItem->save();
            }

            $arrIds[$objOldItem->id] = $objNewItem->id;
        }

        if (!empty($arrIds)) {
            $this->tstamp = $time;
        }

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['copiedCollectionItems']) && is_array($GLOBALS['ISO_HOOKS']['copiedCollectionItems'])) {
            foreach ($GLOBALS['ISO_HOOKS']['copiedCollectionItems'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($objSource, $this, $arrIds);
            }
        }

        $this->clearCache();

        return $arrIds;
    }


    /**
     * Copy product collection surcharges from another collection to this one (e.g. Cart to Order)
     *
     * @param IsotopeProductCollection $objSource
     * @param array                    $arrItemMap
     *
     * @return int[]
     *
     * @deprecated Deprecated since version 2.2, to be removed in 3.0.
     *             Surcharges are calculated on the fly, so it does not make sense to copy them from another one.
     */
    public function copySurchargesFrom(IsotopeProductCollection $objSource, array $arrItemMap = array())
    {
        $this->ensureNotLocked();

        $arrIds  = array();
        $time    = time();
        $sorting = 128;

        foreach ($objSource->getSurcharges() as $objSourceSurcharge) {
            $objSurcharge          = clone $objSourceSurcharge;
            $objSurcharge->pid     = $this->id;
            $objSurcharge->tstamp  = $time;
            $objSurcharge->sorting = $sorting;

            // Convert surcharge amount for individual product IDs
            $objSurcharge->convertCollectionItemIds($arrItemMap);

            $objSurcharge->save();

            $arrIds[$sorting] = $objSurcharge->id;

            $sorting += 128;
        }

        // Empty cache
        $this->arrSurcharges = null;
        $this->arrCache = null;

        return $arrIds;
    }


    /**
     * Add all products in the collection to the given scale
     *
     * @param Scale $objScale
     *
     * @return Scale
     */
    public function addToScale(Scale $objScale = null)
    {
        if (null === $objScale) {
            $objScale = new Scale();
        }

        foreach ($this->getItems() as $objItem) {
            if (!$objItem->hasProduct()) {
                continue;
            }

            $objProduct = $objItem->getProduct();

            if ($objProduct instanceof WeightAggregate) {
                $objWeight = $objProduct->getWeight();

                if (null !== $objWeight) {
                    for ($i = 0; $i < $objItem->quantity; $i++) {
                        $objScale->add($objWeight);
                    }
                }

            } elseif ($objProduct instanceof Weighable) {
                for ($i = 0; $i < $objItem->quantity; $i++) {
                    $objScale->add($objProduct);
                }
            }
        }

        return $objScale;
    }


    /**
     * Add the collection to a template
     *
     * @param \Template $objTemplate
     * @param array     $arrConfig
     */
    public function addToTemplate(\Template $objTemplate, array $arrConfig = array())
    {
        $arrGalleries = array();
        $arrItems     = $this->addItemsToTemplate($objTemplate, $arrConfig['sorting']);

        $objTemplate->collection = $this;
        $objTemplate->config     = ($this->getRelated('config_id') || Isotope::getConfig());
        $objTemplate->surcharges = \Isotope\Frontend::formatSurcharges($this->getSurcharges());
        $objTemplate->subtotal   = Isotope::formatPriceWithCurrency($this->getSubtotal());
        $objTemplate->total      = Isotope::formatPriceWithCurrency($this->getTotal());

        $objTemplate->generateAttribute = function ($strAttribute, ProductCollectionItem $objItem) {

            if (!$objItem->hasProduct()) {
                return '';
            }

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strAttribute];

            if (!($objAttribute instanceof IsotopeAttribute)) {
                throw new \InvalidArgumentException($strAttribute . ' is not a valid attribute');
            }

            return $objAttribute->generate($objItem->getProduct());
        };

        $objTemplate->getGallery = function ($strAttribute, ProductCollectionItem $objItem) use ($arrConfig, &$arrGalleries) {

            if (!$objItem->hasProduct()) {
                return new \Isotope\Model\Gallery\Standard();
            }

            $strCacheKey         = 'product' . $objItem->product_id . '_' . $strAttribute;
            $arrConfig['jumpTo'] = $objItem->getRelated('jumpTo');

            if (!isset($arrGalleries[$strCacheKey])) {
                $arrGalleries[$strCacheKey] = Gallery::createForProductAttribute(
                    $objItem->getProduct(),
                    $strAttribute,
                    $arrConfig
                );
            }

            return $arrGalleries[$strCacheKey];
        };

        // !HOOK: allow overriding of the template
        if (isset($GLOBALS['ISO_HOOKS']['addCollectionToTemplate']) && is_array($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'])) {
            foreach ($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($objTemplate, $arrItems, $this);
            }
        }
    }

    /**
     * Add an error message
     *
     * @param string
     */
    public function addError($strError)
    {
        $this->arrErrors[] = $strError;
    }

    /**
     * Check if collection or any item has errors
     *
     * @return bool
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
     *
     * @return array
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
     * Loop over items and add them to template
     *
     * @param \Template $objTemplate
     * @param Callable  $varCallable
     *
     * @return array
     */
    protected function addItemsToTemplate(\Template $objTemplate, $varCallable = null)
    {
        $arrItems = array();

        foreach ($this->getItems($varCallable) as $objItem) {
            $arrItems[] = $this->generateItem($objItem);
        }

        RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo($arrItems);

        $objTemplate->items = $arrItems;

        return $arrItems;
    }

    /**
     * Generate item array for template
     *
     * @param ProductCollectionItem $objItem
     *
     * @return array
     */
    protected function generateItem(ProductCollectionItem $objItem)
    {
        $blnHasProduct = $objItem->hasProduct();
        $objProduct    = $objItem->getProduct();

        // Set the active product for insert tags replacement
        if ($blnHasProduct) {
            Product::setActive($objProduct);
        }

        $arrCSS = ($blnHasProduct ? deserialize($objProduct->cssID, true) : array());

        $arrItem = array(
            'id'                => $objItem->id,
            'sku'               => $objItem->getSku(),
            'name'              => $objItem->getName(),
            'options'           => Isotope::formatOptions($objItem->getOptions()),
            'quantity'          => $objItem->quantity,
            'price'             => Isotope::formatPriceWithCurrency($objItem->getPrice()),
            'tax_free_price'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreePrice()),
            'total'             => Isotope::formatPriceWithCurrency($objItem->getTotalPrice()),
            'tax_free_total'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreeTotalPrice()),
            'tax_id'            => $objItem->tax_id,
            'hasProduct'        => $blnHasProduct,
            'product'           => $objProduct,
            'item'              => $objItem,
            'raw'               => $objItem->row(),
            'rowClass'          => trim('product ' . (($blnHasProduct && $objProduct->isNew()) ? 'new ' : '') . $arrCSS[1]),
        );

        if (null !== $objItem->getRelated('jumpTo') && $blnHasProduct) {
            $arrItem['href'] = $objProduct->generateUrl($objItem->getRelated('jumpTo'));
        }

        Product::unsetActive();

        return $arrItem;
    }

    /**
     * Get a collection-specific error message for items with errors
     *
     * @return string
     */
    protected function getMessageIfErrorsInItems()
    {
        return $GLOBALS['TL_LANG']['ERR']['collectionErrorInItems'];
    }

    /**
     * Generate the next higher Document Number based on existing records
     *
     * @param string $strPrefix
     * @param int    $intDigits
     *
     * @return string
     */
    protected function generateDocumentNumber($strPrefix, $intDigits)
    {
        if ($this->arrData['document_number'] != '') {
            return $this->arrData['document_number'];
        }

        // !HOOK: generate a custom order ID
        if (isset($GLOBALS['ISO_HOOKS']['generateDocumentNumber']) && is_array($GLOBALS['ISO_HOOKS']['generateDocumentNumber'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateDocumentNumber'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $strOrderId  = $objCallback->$callback[1]($this, $strPrefix, $intDigits);

                if ($strOrderId !== false) {
                    $this->arrData['document_number'] = $strOrderId;
                    break;
                }
            }
        }

        if ($this->arrData['document_number'] == '') {
            $strPrefix = Haste::getInstance()->call('replaceInsertTags', array($strPrefix, true));
            $intPrefix = utf8_strlen($strPrefix);

            // Lock tables so no other order can get the same ID
            \Database::getInstance()->lockTables(array(static::$strTable => 'WRITE'));

            // Retrieve the highest available order ID
            $objMax = \Database::getInstance()->prepare("
                SELECT document_number
                FROM " . static::$strTable . "
                WHERE
                    type=?
                    " . ($strPrefix != '' ? " AND document_number LIKE '$strPrefix%'" : '') . "
                    AND store_id=?
                ORDER BY CAST(" . ($strPrefix != '' ? "SUBSTRING(document_number, " . ($intPrefix+1) . ")" : 'document_number') . " AS UNSIGNED) DESC
            ")->limit(1)->execute(
                array_search(get_called_class(), static::getModelTypes()),
                Isotope::getCart()->store_id
            );

            $intMax = (int) substr($objMax->document_number, $intPrefix);

            $this->arrData['document_number'] = $strPrefix . str_pad($intMax + 1, $intDigits, '0', STR_PAD_LEFT);
        }

        \Database::getInstance()->prepare("
            UPDATE " . static::$strTable . " SET document_number=? WHERE id=?
        ")->execute($this->arrData['document_number'], $this->id);

        \Database::getInstance()->unlockTables();

        return $this->arrData['document_number'];
    }

    /**
     * Generate a unique ID for this collection
     *
     * @return string
     */
    protected function generateUniqueId()
    {
        if ($this->arrData['uniqid'] != '') {
            return $this->arrData['uniqid'];
        }

        return uniqid('', true);
    }

    /**
     * Prevent modifying a locked collection
     *
     * @throws \BadMethodCallException
     */
    protected function ensureNotLocked()
    {
        if ($this->isLocked()) {
            throw new \BadMethodCallException('Product collection is locked');
        }
    }

    /**
     * Make sure the addresses belong to this collection only, so they will never be modified
     */
    protected function createPrivateAddresses()
    {
        $this->ensureNotLocked();

        if (!$this->id) {
            throw new \UnderflowException('Product collection must be saved before creating unique addresses.');
        }

        $objBillingAddress  = $this->getBillingAddress();
        $objShippingAddress = $this->getShippingAddress();

        // Store address in address book
        if ($this->iso_addToAddressbook && $this->member > 0) {

            if (null !== $objBillingAddress && $objBillingAddress->ptable != \MemberModel::getTable()) {
                $objAddress         = clone $objBillingAddress;
                $objAddress->pid    = $this->member;
                $objAddress->tstamp = time();
                $objAddress->ptable = \MemberModel::getTable();
                $objAddress->save();

                $this->updateDefaultAddress($objAddress);
            }

            if (null !== $objBillingAddress
                && null !== $objShippingAddress
                && $objBillingAddress->id != $objShippingAddress->id
                && $objShippingAddress->ptable != \MemberModel::getTable()
            ) {
                $objAddress         = clone $objShippingAddress;
                $objAddress->pid    = $this->member;
                $objAddress->tstamp = time();
                $objAddress->ptable = \MemberModel::getTable();
                $objAddress->save();

                $this->updateDefaultAddress($objAddress);
            }
        }

        if (null !== $objBillingAddress && ($objBillingAddress->ptable != static::$strTable || $objBillingAddress->pid != $this->id)) {

            $objNew         = clone $objBillingAddress;
            $objNew->pid    = $this->id;
            $objNew->tstamp = time();
            $objNew->ptable = static::$strTable;
            $objNew->save();

            $this->setBillingAddress($objNew);

            if (null !== $objShippingAddress && $objBillingAddress->id == $objShippingAddress->id) {
                $this->setShippingAddress($objNew);

                // Return here, we do not need to check shipping address
                return;
            }
        }

        if (null !== $objShippingAddress && ($objShippingAddress->ptable != static::$strTable || $objShippingAddress->pid != $this->id)) {

            $objNew         = clone $objShippingAddress;
            $objNew->pid    = $this->id;
            $objNew->tstamp = time();
            $objNew->ptable = static::$strTable;
            $objNew->save();

            $this->setShippingAddress($objNew);
        }
    }

    /**
     * Mark existing addresses as not default if the new address is default
     *
     * @param Address $objAddress
     */
    protected function updateDefaultAddress(Address $objAddress)
    {
        $arrSet = array();

        if ($objAddress->isDefaultBilling) {
            $arrSet['isDefaultBilling'] = '';
        }

        if ($objAddress->isDefaultShipping) {
            $arrSet['isDefaultShipping'] = '';
        }

        if (!empty($arrSet)) {
            // @todo restore foratting when #6623 is fixed in Contao core
            \Database::getInstance()->prepare("UPDATE " . $objAddress->getTable() . " %s WHERE pid=? AND ptable=? AND store_id=? AND id!=?")
                     ->set($arrSet)
                     ->execute($this->member, \MemberModel::getTable(), $this->store_id, $objAddress->id);
        }
    }

    /**
     * Clear all cache properties
     */
    protected function clearCache()
    {
        $this->arrItems = null;
        $this->arrSurcharges = null;
        $this->arrCache = null;
        $this->arrErrors = array();
        $this->objPayment = false;
        $this->objShipping = false;
    }

    /**
     * Initialize a new collection and duplicate everything from the source
     *
     * @param IsotopeProductCollection $objSource
     *
     * @return static
     */
    public static function createFromCollection(IsotopeProductCollection $objSource)
    {
        $objCollection = new static();
        $objConfig = $objSource->getRelated('config_id');

        if (null === $objConfig) {
            $objConfig = Isotope::getConfig();
        }

        $objCollection->source_collection_id = (int) $objSource->id;
        $objCollection->config_id            = (int) $objConfig->id;
        $objCollection->store_id             = (int) $objSource->store_id;
        $objCollection->member               = (int) $objSource->member;

        $objCollection->setShippingMethod($objSource->getShippingMethod());
        $objCollection->setPaymentMethod($objSource->getPaymentMethod());

        $objCollection->setShippingAddress($objSource->getShippingAddress());
        $objCollection->setBillingAddress($objSource->getBillingAddress());

        $arrItemIds = $objCollection->copyItemsFrom($objSource);

        $objCollection->updateDatabase();

        // HOOK: order status has been updated
        if (isset($GLOBALS['ISO_HOOKS']['createFromProductCollection']) && is_array($GLOBALS['ISO_HOOKS']['createFromProductCollection'])) {
        	foreach ($GLOBALS['ISO_HOOKS']['createFromProductCollection'] as $callback) {
        		$objCallback = \System::importStatic($callback[0]);
        		$objCallback->$callback[1]($objCollection, $objSource, $arrItemIds);
        	}
        }

        return $objCollection;
    }


    /**
     * Method that returns a closure to sort product collection items
     *
     * @param string $strOrderBy
     *
     * @return \Closure|null
     */
    public static function getItemsSortingCallable($strOrderBy = 'asc_id')
    {
        list($direction, $attribute) = explode('_', $strOrderBy, 2);

        if ($direction == 'asc') {

            return function ($arrItems) use ($attribute) {
                uasort($arrItems, function ($objItem1, $objItem2) use ($attribute) {
                    if ($objItem1->$attribute == $objItem2->$attribute) {
                        return 0;
                    }

                    return $objItem1->$attribute < $objItem2->$attribute ? -1 : 1;
                });

                return $arrItems;
            };

        } elseif ($direction == 'desc') {

            return function ($arrItems) use ($attribute) {
                uasort($arrItems, function ($objItem1, $objItem2) use ($attribute) {
                    if ($objItem1->$attribute == $objItem2->$attribute) {
                        return 0;
                    }

                    return $objItem1->$attribute > $objItem2->$attribute ? -1 : 1;
                });

                return $arrItems;
            };
        }

        return null;
    }
}
