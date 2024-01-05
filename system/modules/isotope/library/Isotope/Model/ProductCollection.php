<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Controller;
use Contao\Database;
use Contao\MemberModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Haste\Generator\RowClass;
use Haste\Units\Mass\Scale;
use Haste\Units\Mass\Weighable;
use Haste\Units\Mass\WeightAggregate;
use Haste\Util\Format;
use Isotope\CompatibilityHelper;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\Gallery\Standard as StandardGallery;
use Isotope\Model\ProductCollectionSurcharge\Tax;
use Contao\Model\Registry;

/**
 * Class ProductCollection
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property int    $member
 * @property int    $store_id
 * @property int    $locked
 * @property mixed  $settings
 * @property int    $source_collection_id
 * @property string $uniqid
 * @property int    $config_id
 * @property int    $payment_id
 * @property int    $shipping_id
 * @property int    $billing_address_id
 * @property int    $shipping_address_id
 * @property float  $subtotal
 * @property float  $tax_free_subtotal
 * @property float  $total
 * @property float  $tax_free_total
 * @property string $currency
 * @property string $language
 *
 * @property int|array $nc_notification
 * @property bool      $iso_addToAddressbook
 * @property array     $iso_checkout_skippable
 * @property array     $email_data
 * @property int       $orderdetails_page
 */
abstract class ProductCollection extends TypeAgent implements IsotopeProductCollection
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
    public function __construct(\Contao\Database\Result $objResult = null)
    {
        parent::__construct($objResult);

        $this->arrData['uniqid'] = $this->generateUniqueId();

        // Do not use __destruct, because Database object might be destructed first
        // see http://github.com/contao/core/issues/2236
        if (CompatibilityHelper::isFrontend()) {
            register_shutdown_function(array($this, 'updateDatabase'), false);
        }
    }

    /**
     * Prevent cloning because we can't copy items etc.
     *
     * @throws \LogicException because ProductCollection cannot be cloned
     */
    /** @noinspection MagicMethodsValidityInspection */
    public function __clone()
    {
        throw new \LogicException(
            'Product collections can\'t be cloned, you should probably use ProductCollection::createFromCollection'
        );
    }

    /**
     * Shutdown function to update prices of items and collection
     *
     * @param boolean $blnCreate If true create Model even if not in registry or not saved at all
     */
    public function updateDatabase($blnCreate = true)
    {
        if (!$this->blnPreventSaving
            && !$this->isLocked()
            && (Registry::getInstance()->isRegistered($this) || $blnCreate)
        ) {
            foreach ($this->getItems() as $objItem) {
                if (!$objItem->hasProduct()) {
                    continue;
                }

                $objItem->price          = $objItem->getPrice();
                $objItem->tax_free_price = $objItem->getTaxFreePrice();
                $objItem->name           = $objItem->getName();
                $objItem->save();
            }

            // First call to __set for tstamp will truncate the cache
            $this->tstamp            = time();
            $this->subtotal          = $this->getSubtotal();
            $this->tax_free_subtotal = $this->getTaxFreeSubtotal();
            $this->total             = $this->getTotal();
            $this->tax_free_total    = $this->getTaxFreeTotal();
            $this->currency          = (string) $this->getConfig()->currency;

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
        if ('locked' === $strKey) {
            throw new \InvalidArgumentException('Cannot change lock status of collection');
        }

        if ('document_number' === $strKey) {
            throw new \InvalidArgumentException(
                'Cannot change document number of a collection, must be generated using generateDocumentNumber()'
            );
        }

        $this->clearCache();

        parent::markModified($strKey);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getUniqueId()
    {
        return $this->uniqid;
    }

    /**
     * @inheritdoc
     */
    public function getMember()
    {
        if (0 === (int) $this->member) {
            return null;
        }

        return MemberModel::findByPk($this->member);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return (int) $this->store_id;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        try {
            return $this->getRelated('config_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function isLocked()
    {
        return null !== $this->locked;
    }

    /**
     * @inheritdoc
     */
    public function getLockTime()
    {
        return $this->locked;
    }


    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return 0 === \count($this->getItems());
    }

    /**
     * Return payment method for this collection
     *
     * @return IsotopePayment|null
     */
    public function getPaymentMethod()
    {
        if (false === $this->objPayment) {
            try {
                $this->objPayment = $this->getRelated('payment_id');
            } catch (\Exception $e) {
                $this->objPayment = null;
            }
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
        $this->payment_id = (null === $objPayment ? 0 : $objPayment->getId());
        $this->objPayment = $objPayment;
    }

    /**
     * Return surcharge for current payment method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getPaymentSurcharge()
    {
        return $this->hasPayment() ? $this->getPaymentMethod()->getSurcharge($this) : null;
    }

    /**
     * Return boolean whether collection has payment
     *
     * @return bool
     */
    public function hasPayment()
    {
        return null !== $this->getPaymentMethod();
    }

    /**
     * Return boolean whether collection requires payment
     *
     * @return bool
     */
    public function requiresPayment()
    {
        return $this->getTotal() > 0;
    }

    /**
     * Return shipping method for this collection
     *
     * @return IsotopeShipping|null
     */
    public function getShippingMethod()
    {
        if (false === $this->objShipping) {
            try {
                $this->objShipping = $this->getRelated('shipping_id');
            } catch (\Exception $e) {
                $this->objShipping = null;
            }
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
        $this->shipping_id = (null === $objShipping ? 0 : $objShipping->getId());
        $this->objShipping = $objShipping;
    }

    /**
     * Return surcharge for current shipping method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getShippingSurcharge()
    {
        return $this->hasShipping() ? $this->getShippingMethod()->getSurcharge($this) : null;
    }

    /**
     * Return boolean whether collection has shipping
     *
     * @return bool
     */
    public function hasShipping()
    {
        return null !== $this->getShippingMethod();
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
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {
                if ($objItem->hasProduct() && !$objItem->getProduct()->isExemptFromShipping()) {
                    $this->arrCache['requiresShipping'] = true;
                    break;
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
        if (!$this->billing_address_id) {
            return null;
        }

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
     * Return boolean whether collection requires a shipping address
     *
     * @return bool
     */
    public function requiresShippingAddress()
    {
        if (!$this->requiresShipping()) {
            return false;
        }

        if (!isset($this->arrCache['requiresShippingAddress'])) {
            $this->arrCache['requiresShippingAddress'] = true;
            $arrItems = $this->getItems();

            foreach ($arrItems as $objItem) {
                $product = $objItem->getProduct();
                if ($product instanceof IsotopeProduct && \method_exists($product, 'isPickupOnly') && $product->isPickupOnly()) {
                    $this->arrCache['requiresShippingAddress'] = false;
                    break;
                }
            }
        }

        return $this->arrCache['requiresShippingAddress'];
    }

    /**
     * Get shipping address for collection
     *
     * @return  Address|null
     */
    public function getShippingAddress()
    {
        if (!$this->shipping_address_id || !$this->requiresShippingAddress()) {
            return null;
        }

        return $this->getRelated('shipping_address_id');
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
     * Returns the generated document number or empty string if not available.
     *
     * @return string
     */
    public function getDocumentNumber()
    {
        return (string) $this->arrData['document_number'];
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
        } elseif ($this->member > 0
            && ($objMember = MemberModel::findByPk($this->member)) !== null
            && $objMember->email != ''
        ) {
            $strName  = $objMember->firstname . ' ' . $objMember->lastname;
            $strEmail = $objMember->email;
        }

        if (trim($strName) != '') {
            // Romanize friendly name to prevent email issues
            $strName = html_entity_decode($strName, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
            $strName = StringUtil::stripInsertTags($strName);
            $strName = utf8_romanize($strName);
            $strName = preg_replace('/[^A-Za-z0-9.!#$%&\'*+-\/=?^_ `{|}~]+/i', '_', $strName);

            $strEmail = sprintf('"%s" <%s>', $strName, $strEmail);
        }

        // !HOOK: determine email recipient for collection
        if (isset($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['emailRecipientForCollection'] as $callback) {
                $strEmail = System::importStatic($callback[0])->{$callback[1]}($strEmail, $this);
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
            $this->arrCache['countItems'] = ProductCollectionItem::countBy('pid', (int) $this->id);
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
            $this->arrCache['sumItemsQuantity'] = ProductCollectionItem::sumBy('quantity', 'pid', (int) $this->id);
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
        $this->arrData = array_merge(StringUtil::deserialize($arrData['settings'] ?? [], true), $this->arrData);

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
        if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && \is_array($GLOBALS['ISO_HOOKS']['saveCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this);
            }
        }

        $arrDbFields = Database::getInstance()->getFieldNames(static::$strTable);
        $arrModified = array_diff_key($this->arrModified, array_flip($arrDbFields));

        if (!empty($arrModified)) {
            $arrSettings = StringUtil::deserialize($this->settings, true);
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
     *
     * @throws \BadMethodCallException if the product collection is locked.
     */
    public function delete($blnForce = false)
    {
        if (!$blnForce) {
            $this->ensureNotLocked();

            // !HOOK: additional functionality when deleting a collection
            if (isset($GLOBALS['ISO_HOOKS']['deleteCollection'])
                && \is_array($GLOBALS['ISO_HOOKS']['deleteCollection'])
            ) {
                foreach ($GLOBALS['ISO_HOOKS']['deleteCollection'] as $callback) {
                    $blnRemove = System::importStatic($callback[0])->{$callback[1]}($this);

                    if ($blnRemove === false) {
                        return 0;
                    }
                }
            }
        }

        $intPid          = $this->id;
        $intAffectedRows = parent::delete();

        if ($intAffectedRows > 0 && $intPid > 0) {
            Database::getInstance()->query("
                DELETE FROM tl_iso_product_collection_download
                WHERE pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=$intPid)
            ");
            Database::getInstance()->query(
                "DELETE FROM tl_iso_product_collection_item WHERE pid=$intPid"
            );
            Database::getInstance()->query(
                "DELETE FROM tl_iso_product_collection_surcharge WHERE pid=$intPid"
            );
            Database::getInstance()->query(
                "DELETE FROM tl_iso_address WHERE ptable='" . static::$strTable . "' AND pid=$intPid"
            );
        }

        $this->arrCache      = array();
        $this->arrItems      = null;
        $this->arrSurcharges = null;

        // !HOOK: additional functionality when deleting a collection
        if (isset($GLOBALS['ISO_HOOKS']['postDeleteCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['postDeleteCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['postDeleteCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this, $intPid);
            }
        }

        return $intAffectedRows;
    }

    /**
     * Delete all products in the collection
     *
     * @throws \BadMethodCallException if the product collection is locked.
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
     *
     * @throws \BadMethodCallException if the product collection is locked.
     */
    public function lock()
    {
        $this->ensureNotLocked();

        global $objPage;
        $time = time();

        $this->pageId = (int) $objPage->id;
        $this->language = (string) $GLOBALS['TL_LANGUAGE'];

        $this->createPrivateAddresses();
        $this->updateDatabase();

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
        Database::getInstance()->query(
            "UPDATE tl_iso_product_collection SET locked=$time WHERE id=" . $this->id
        );
        $this->arrData['locked'] = $time;

        // !HOOK: pre-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['collectionLocked']) && \is_array($GLOBALS['ISO_HOOKS']['collectionLocked'])) {
            foreach ($GLOBALS['ISO_HOOKS']['collectionLocked'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this);
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
                $varPrice = $objItem->getTotalPrice();

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
                $varPrice = $objItem->getTaxFreeTotalPrice();

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

            $this->arrCache['total'] = $fltAmount > 0 ? $fltAmount : 0;
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
            $arrSurcharges = $this->getSurcharges();

            if (Config::PRICE_DISPLAY_GROSS === $this->getConfig()->priceDisplay) {
                $fltAmount = $this->getTotal();

                foreach ($arrSurcharges as $objSurcharge) {
                    if ($objSurcharge instanceof Tax) {
                        $fltAmount -= $objSurcharge->total_price;
                    }
                }
            } else {
                $fltAmount = $this->getTaxFreeSubtotal();

                foreach ($arrSurcharges as $objSurcharge) {
                    if ($objSurcharge->addToTotal) {
                        $fltAmount += $objSurcharge->tax_free_total_price;
                    }
                }
            }

            $this->arrCache['taxFreeTotal'] = $fltAmount > 0 ? Isotope::roundPrice($fltAmount) : 0;
        }

        return $this->arrCache['taxFreeTotal'];
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
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
                    if (!$objItem->isAvailable() && !$objItem->hasErrors()) {
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

        return \call_user_func($varCallable, $arrItems);
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
        $strClass = array_search(\get_class($objProduct), Product::getModelTypes(), true);

        $objItem = ProductCollectionItem::findOneBy(
            array('pid=?', 'type=?', 'product_id=?', 'configuration=?'),
            array($this->id, $strClass, $objProduct->getId(), serialize($objProduct->getOptions()))
        );

        return $objItem;
    }

    /**
     * Gets the product collection with given ID if it belongs to this collection.
     *
     * @param int $id
     *
     * @return ProductCollectionItem|null
     */
    public function getItemById($id)
    {
        $items = $this->getItems();

        if (!isset($items[$id])) {
            return null;
        }

        return $items[$id];
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
            return null !== $this->getItemForProduct($objProduct);
        }

        $intId = $objProduct->getProductId();

        foreach ($this->getItems() as $objItem) {
            if ($objItem->hasProduct()
                && ($objItem->getProduct()->getId() == $intId || $objItem->getProduct()->getProductId() == $intId)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a product to the collection
     *
     * @param IsotopeProduct $objProduct
     * @param int            $intQuantity
     * @param array          $arrConfig
     *
     * @return ProductCollectionItem|false
     */
    public function addProduct(IsotopeProduct $objProduct, $intQuantity, array $arrConfig = array())
    {
        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['addProductToCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['addProductToCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['addProductToCollection'] as $callback) {
                $intQuantity = System::importStatic($callback[0])->{$callback[1]}($objProduct, $intQuantity, $this, $arrConfig);
            }
        }

        if ($intQuantity == 0) {
            return false;
        }

        $time         = time();
        $this->tstamp = $time;

        // Make sure collection is in DB before adding product
        if (!Registry::getInstance()->isRegistered($this)) {
            $this->save();
        }

        // Remove uploaded files from session so they are not added to the next product (see #646)
        unset($_SESSION['FILES']);

        $objItem            = $this->getItemForProduct($objProduct);
        $intMinimumQuantity = $objProduct->getMinimumQuantity();

        if (null !== $objItem) {
            if (($objItem->quantity + $intQuantity) < $intMinimumQuantity) {
                Message::addInfo(sprintf(
                    $GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'],
                    $objProduct->getName(),
                    $intMinimumQuantity
                ));
                $intQuantity            = $intMinimumQuantity - $objItem->quantity;
            }

            $objItem->increaseQuantityBy($intQuantity);
        } else {
            if ($intQuantity < $intMinimumQuantity) {
                Message::addInfo(sprintf(
                    $GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'],
                    $objProduct->getName(),
                    $intMinimumQuantity
                ));
                $intQuantity            = $intMinimumQuantity;
            }

            $objItem           = new ProductCollectionItem();
            $objItem->pid      = $this->id;
            $objItem->jumpTo   = isset($arrConfig['jumpTo']) ? (int) $arrConfig['jumpTo']->id : 0;

            $this->setProductForItem($objProduct, $objItem, $intQuantity);
            $objItem->save();

            // Add the new item to our cache
            $this->arrItems[$objItem->id] = $objItem;
        }

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['postAddProductToCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['postAddProductToCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['postAddProductToCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objItem, $intQuantity, $this, $arrConfig);
            }
        }

        return $objItem;
    }

    /**
     * Update product details for a collection item.
     *
     * @param IsotopeProduct        $objProduct
     * @param ProductCollectionItem $objItem
     *
     * @return bool
     */
    public function updateProduct(IsotopeProduct $objProduct, ProductCollectionItem $objItem)
    {
        if ($objItem->pid != $this->id) {
            throw new \InvalidArgumentException('Item does not belong to this collection');
        }

        // !HOOK: additional functionality when updating product in collection
        if (isset($GLOBALS['ISO_HOOKS']['updateProductInCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['updateProductInCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['updateProductInCollection'] as $callback) {
                if (false === System::importStatic($callback[0])->{$callback[1]}($objProduct, $objItem, $this)) {
                    return false;
                }
            }
        }

        $this->setProductForItem($objProduct, $objItem, $objItem->quantity);
        $objItem->save();

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['postUpdateProductInCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['postUpdateProductInCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['postUpdateProductInCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objProduct, $objItem, $this);
            }
        }

        return true;
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
        if (isset($GLOBALS['ISO_HOOKS']['updateItemInCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['updateItemInCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['updateItemInCollection'] as $callback) {
                $arrSet = System::importStatic($callback[0])->{$callback[1]}($objItem, $arrSet, $this);

                if (!\is_array($arrSet) || 0 === \count($arrSet)) {
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
                Message::addInfo(sprintf(
                    $GLOBALS['TL_LANG']['ERR']['productMinimumQuantity'],
                    $objProduct->getName(),
                    $intMinimumQuantity
                ));
                $arrSet['quantity']     = $intMinimumQuantity;
            }
        }

        $arrSet['tstamp'] = time();

        foreach ($arrSet as $k => $v) {
            $objItem->$k = $v;
        }

        $objItem->save();
        $this->tstamp = time();

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['postUpdateItemInCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['postUpdateItemInCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['postUpdateItemInCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objItem, $arrSet['quantity'], $this);
            }
        }

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
     *
     * @throws \BadMethodCallException if the product collection is locked.
     */
    public function deleteItemById($intId)
    {
        $this->ensureNotLocked();

        $arrItems = $this->getItems();

        if (!isset($arrItems[$intId])) {
            return false;
        }

        $objItem = $arrItems[$intId];

        // !HOOK: additional functionality when a product is removed from the collection
        if (isset($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['deleteItemFromCollection'] as $callback) {
                $blnRemove = System::importStatic($callback[0])->{$callback[1]}($objItem, $this);

                if ($blnRemove === false) {
                    return false;
                }
            }
        }

        $objItem->delete();

        unset($this->arrItems[$intId]);

        $this->tstamp = time();

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['postDeleteItemFromCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['postDeleteItemFromCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['postDeleteItemFromCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objItem, $this);
            }
        }

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
                $this->arrSurcharges = [];

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
     *
     * @throws \BadMethodCallException if the product collection is locked.
     */
    public function copyItemsFrom(IsotopeProductCollection $objSource)
    {
        $this->ensureNotLocked();

        $this->updateDatabase();

        // Make sure database table has the latest prices
        $objSource->updateDatabase();

        $time        = time();
        $arrIds      = [];
        $arrOldItems = $objSource->getItems();

        foreach ($arrOldItems as $objOldItem) {

            // !HOOK: additional functionality when copying product to collection
            if (isset($GLOBALS['ISO_HOOKS']['copyCollectionItem'])
                && \is_array($GLOBALS['ISO_HOOKS']['copyCollectionItem'])
            ) {
                foreach ($GLOBALS['ISO_HOOKS']['copyCollectionItem'] as $callback) {
                    if (System::importStatic($callback[0])->{$callback[1]}($objOldItem, $objSource, $this) === false) {
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

        if (\count($arrIds) > 0) {
            $this->tstamp = $time;
        }

        // !HOOK: additional functionality when adding product to collection
        if (isset($GLOBALS['ISO_HOOKS']['copiedCollectionItems'])
            && \is_array($GLOBALS['ISO_HOOKS']['copiedCollectionItems'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['copiedCollectionItems'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objSource, $this, $arrIds);
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
     *
     * @throws \BadMethodCallException if the product collection is locked.
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function addToTemplate(Template $objTemplate, array $arrConfig = [])
    {
        $arrGalleries = array();
        $objConfig    = $this->getRelated('config_id') ?: Isotope::getConfig();
        $arrItems     = $this->addItemsToTemplate($objTemplate, $arrConfig['sorting']);

        $objTemplate->id                = $this->id;
        $objTemplate->collection        = $this;
        $objTemplate->config            = $objConfig;
        $objTemplate->surcharges        = Frontend::formatSurcharges($this->getSurcharges(), $objConfig->currency);
        $objTemplate->subtotal          = Isotope::formatPriceWithCurrency($this->getSubtotal(), true, $objConfig->currency);
        $objTemplate->total             = Isotope::formatPriceWithCurrency($this->getTotal(), true, $objConfig->currency);
        $objTemplate->tax_free_subtotal = Isotope::formatPriceWithCurrency($this->getTaxFreeSubtotal(), true, $objConfig->currency);
        $objTemplate->tax_free_total    = Isotope::formatPriceWithCurrency($this->getTaxFreeTotal(), true, $objConfig->currency);

        $objTemplate->hasAttribute = function ($strAttribute, ProductCollectionItem $objItem) {
            if (!$objItem->hasProduct()) {
                return false;
            }

            $objProduct = $objItem->getProduct();

            return \in_array($strAttribute, $objProduct->getAttributes(), true)
                || \in_array($strAttribute, $objProduct->getVariantAttributes(), true);
        };

        $objTemplate->generateAttribute = function (
            $strAttribute,
            ProductCollectionItem $objItem,
            array $arrOptions = array()
        ) {
            if (!$objItem->hasProduct()) {
                return '';
            }

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strAttribute];

            if (!($objAttribute instanceof IsotopeAttribute)) {
                throw new \InvalidArgumentException($strAttribute . ' is not a valid attribute');
            }

            return $objAttribute->generate($objItem->getProduct(), $arrOptions);
        };

        $objTemplate->getGallery = function (
            $strAttribute,
            ProductCollectionItem $objItem
        ) use (
            $arrConfig,
            &$arrGalleries
        ) {
            if (!$objItem->hasProduct()) {
                return new StandardGallery();
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

        $objTemplate->attributeLabel = function ($name, array $options = []) {
            /** @var Attribute $attribute */
            $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$name] ?? null;

            if (!$attribute instanceof IsotopeAttribute) {
                return Format::dcaLabel('tl_iso_product', $name);
            }

            return $attribute->getLabel($options);
        };

        $objTemplate->attributeValue = function ($name, $value, array $options = []) {
            /** @var Attribute $attribute */
            $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$name] ?? null;

            if (!$attribute instanceof IsotopeAttribute) {
                return Format::dcaValue('tl_iso_product', $name, $value);
            }

            return $attribute->generateValue($value, $options);
        };

        // !HOOK: allow overriding of the template
        if (isset($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'])
            && \is_array($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objTemplate, $arrItems, $this, $arrConfig);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addError($message)
    {
        $this->arrErrors[] = $message;
    }

    /**
     * @inheritdoc
     */
    public function hasErrors()
    {
        if (\count($this->arrErrors) > 0) {
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
     * @inheritdoc
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
     * @param Callable  $varCallable
     *
     * @return array
     */
    protected function addItemsToTemplate(Template $objTemplate, $varCallable = null)
    {
        $taxIds   = array();
        $arrItems = array();

        foreach ($this->getItems($varCallable) as $objItem) {
            $item = $this->generateItem($objItem);

            $taxIds[]   = $item['tax_id'];
            $arrItems[] = $item;
        }

        RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo($arrItems);

        $objTemplate->items         = $arrItems;
        $objTemplate->total_tax_ids = \count(array_unique($taxIds));

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
        $objConfig     = $this->getRelated('config_id') ?: Isotope::getConfig();
        $arrCSS        = ($blnHasProduct ? StringUtil::deserialize($objProduct->cssID, true) : array());

        // Set the active product for insert tags replacement
        if ($blnHasProduct) {
            Product::setActive($objProduct);
        }

        $arrItem = array(
            'id'                => $objItem->id,
            'sku'               => $objItem->getSku(),
            'name'              => $objItem->getName(),
            'options'           => Isotope::formatOptions($objItem->getOptions()),
            'configuration'     => $objItem->getConfiguration(),
            'attributes'        => $objItem->getAttributes(),
            'quantity'          => $objItem->quantity,
            'price'             => Isotope::formatPriceWithCurrency($objItem->getPrice(), true, $objConfig->currency),
            'tax_free_price'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreePrice(), true, $objConfig->currency),
            'original_price'    => Isotope::formatPriceWithCurrency($objItem->getOriginalPrice(), true, $objConfig->currency),
            'total'             => Isotope::formatPriceWithCurrency($objItem->getTotalPrice(), true, $objConfig->currency),
            'tax_free_total'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreeTotalPrice(), true, $objConfig->currency),
            'original_total'    => Isotope::formatPriceWithCurrency($objItem->getTotalOriginalPrice(), true, $objConfig->currency),
            'tax_id'            => $objItem->tax_id,
            'href'              => false,
            'hasProduct'        => $blnHasProduct,
            'product'           => $objProduct,
            'item'              => $objItem,
            'raw'               => $objItem->row(),
            'rowClass'          => trim('product ' . (($blnHasProduct && $objProduct->isNew()) ? 'new ' : '') . ($arrCSS[1] ?? ''))
        );

        if ($blnHasProduct && null !== $objItem->getRelated('jumpTo') && $objProduct->isAvailableInFrontend()) {
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
     * @throws \Exception
     */
    protected function generateDocumentNumber($strPrefix, $intDigits)
    {
        if ($this->arrData['document_number'] != '') {
            return $this->arrData['document_number'];
        }

        // !HOOK: generate a custom order ID
        if (isset($GLOBALS['ISO_HOOKS']['generateDocumentNumber'])
            && \is_array($GLOBALS['ISO_HOOKS']['generateDocumentNumber'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['generateDocumentNumber'] as $callback) {
                $strOrderId  = System::importStatic($callback[0])->{$callback[1]}($this, $strPrefix, $intDigits);

                if ($strOrderId !== false) {
                    $this->arrData['document_number'] = $strOrderId;
                    break;
                }
            }
        }

        try {
            if ($this->arrData['document_number'] == '') {
                $strPrefix = Controller::replaceInsertTags($strPrefix, false);
                $intPrefix = utf8_strlen($strPrefix);

                // Lock tables so no other order can get the same ID
                Database::getInstance()->lockTables(array(static::$strTable => 'WRITE'));

                $prefixCondition = ($strPrefix != '' ? " AND document_number LIKE '$strPrefix%'" : '');

                // Retrieve the highest available order ID
                $objMax = Database::getInstance()
                    ->prepare("
                        SELECT document_number
                        FROM tl_iso_product_collection
                        WHERE
                            type=?
                            $prefixCondition
                            AND store_id=?
                        ORDER BY CAST(" . ($strPrefix != '' ? 'SUBSTRING(document_number, ' . ($intPrefix + 1) . ')' : 'document_number') . ' AS UNSIGNED) DESC
                    ')
                    ->limit(1)
                    ->execute(
                        array_search(\get_called_class(), static::getModelTypes(), true),
                        $this->store_id
                    )
                ;

                $intMax = (int) substr($objMax->document_number, $intPrefix);

                $this->arrData['document_number'] = $strPrefix . str_pad($intMax + 1, $intDigits, '0', STR_PAD_LEFT);
            }

            Database::getInstance()
                ->prepare('UPDATE tl_iso_product_collection SET document_number=? WHERE id=?')
                ->execute($this->arrData['document_number'], $this->id)
            ;

            Database::getInstance()->unlockTables();

        } catch (\Exception $e) {
            // Make sure tables are always unlocked
            Database::getInstance()->unlockTables();

            throw $e;
        }

        return $this->arrData['document_number'];
    }

    /**
     * Generate a unique ID for this collection
     *
     * @return string
     */
    protected function generateUniqueId()
    {
        if (!empty($this->arrData['uniqid'])) {
            return $this->arrData['uniqid'];
        }

        return uniqid('', true);
    }

    /**
     * Prevent modifying a locked collection
     *
     * @throws \BadMethodCallException if the collection is locked.
     */
    protected function ensureNotLocked()
    {
        if ($this->isLocked()) {
            throw new \BadMethodCallException('Product collection is locked');
        }
    }

    /**
     * Make sure the addresses belong to this collection only, so they will never be modified
     *
     * @throws \UnderflowException if collection is not saved (not in DB)
     * @throws \BadMethodCallException if the product collection is locked.
     */
    protected function createPrivateAddresses()
    {
        $this->ensureNotLocked();

        if (!$this->id) {
            throw new \UnderflowException('Product collection must be saved before creating unique addresses.');
        }

        $canSkip = StringUtil::deserialize($this->iso_checkout_skippable, true);
        $objBillingAddress  = $this->getBillingAddress();
        $objShippingAddress = $this->getShippingAddress();

        // Store address in address book
        if ($this->iso_addToAddressbook && $this->member > 0) {
            if (null !== $objBillingAddress
                && $objBillingAddress->ptable != MemberModel::getTable()
                && !\in_array('billing_address', $canSkip, true)
            ) {
                $objAddress         = clone $objBillingAddress;
                $objAddress->pid    = $this->member;
                $objAddress->tstamp = time();
                $objAddress->ptable = MemberModel::getTable();
                $objAddress->store_id = $this->store_id;
                $objAddress->save();

                $this->updateDefaultAddress($objAddress);
            }

            if (null !== $objBillingAddress
                && null !== $objShippingAddress
                && $objBillingAddress->id != $objShippingAddress->id
                && $objShippingAddress->ptable != MemberModel::getTable()
                && !\in_array('shipping_address', $canSkip, true)
            ) {
                $objAddress         = clone $objShippingAddress;
                $objAddress->pid    = $this->member;
                $objAddress->tstamp = time();
                $objAddress->ptable = MemberModel::getTable();
                $objAddress->store_id = $this->store_id;
                $objAddress->save();

                $this->updateDefaultAddress($objAddress);
            }
        }

        /** @var Config $config */
        $config         = $this->getRelated('config_id');
        $billingFields  = (null === $config) ? array() : $config->getBillingFields();
        $shippingFields = (null === $config) ? array() : $config->getShippingFields();

        if (null !== $objBillingAddress
            && ($objBillingAddress->ptable != static::$strTable || $objBillingAddress->pid != $this->id)
        ) {
            $arrData = array_intersect_key(
                $objBillingAddress->row(),
                array_merge(array_flip($billingFields), ['country' => ''])
            );

            $objNew = new Address();
            $objNew->setRow($arrData);

            $objNew->pid      = $this->id;
            $objNew->tstamp   = time();
            $objNew->ptable   = static::$strTable;
            $objNew->store_id = $this->store_id;
            $objNew->save();

            $this->setBillingAddress($objNew);

            if (null !== $objShippingAddress && $objBillingAddress->id == $objShippingAddress->id) {
                $this->setShippingAddress($objNew);

                // Stop here, we do not need to check shipping address
                return;
            }
        }

        if (null !== $objShippingAddress
            && ($objShippingAddress->ptable != static::$strTable || $objShippingAddress->pid != $this->id)
        ) {
            $arrData = array_intersect_key(
                $objShippingAddress->row(),
                array_merge(array_flip($shippingFields), ['country' => ''])
            );

            $objNew = new Address();
            $objNew->setRow($arrData);

            $objNew->pid      = $this->id;
            $objNew->tstamp   = time();
            $objNew->ptable   = static::$strTable;
            $objNew->store_id = $this->store_id;
            $objNew->save();

            $this->setShippingAddress($objNew);
        } elseif (null === $objShippingAddress) {
            // Make sure to set the shipping address to null if collection has no shipping
            // see isotope/core#2014
            $this->setShippingAddress(null);
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

        if (\count($arrSet) > 0) {
            Database::getInstance()
                ->prepare('UPDATE tl_iso_address %s WHERE pid=? AND ptable=? AND store_id=? AND id!=?')
                ->set($arrSet)
                ->execute($this->member, MemberModel::getTable(), $this->store_id, $objAddress->id)
            ;
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
        $objConfig = $objSource->getConfig();

        if (null === $objConfig) {
            $objConfig = Isotope::getConfig();
        }

        $member = $objSource->getMember();

        $objCollection->source_collection_id = $objSource->getId();
        $objCollection->config_id            = (int) $objConfig->id;
        $objCollection->store_id             = (int) $objSource->getStoreId();
        $objCollection->member               = (null === $member ? 0 : $member->id);

        if ($objCollection instanceof IsotopeOrderableCollection
            && $objSource instanceof  IsotopeOrderableCollection)
        {
            $objCollection->setShippingMethod($objSource->getShippingMethod());
            $objCollection->setPaymentMethod($objSource->getPaymentMethod());

            $objCollection->setShippingAddress($objSource->getShippingAddress());
            $objCollection->setBillingAddress($objSource->getBillingAddress());
        }

        $arrItemIds = $objCollection->copyItemsFrom($objSource);

        $objCollection->updateDatabase();

        // HOOK: order status has been updated
        if (isset($GLOBALS['ISO_HOOKS']['createFromProductCollection'])
            && \is_array($GLOBALS['ISO_HOOKS']['createFromProductCollection'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['createFromProductCollection'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objCollection, $objSource, $arrItemIds);
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
        [$direction, $attribute] = explode('_', $strOrderBy, 2) + [null, null];

        if ('asc' === $direction) {
            return function ($arrItems) use ($attribute) {
                uasort($arrItems, function ($objItem1, $objItem2) use ($attribute) {
                    if ($objItem1->$attribute == $objItem2->$attribute) {
                        return 0;
                    }

                    return $objItem1->$attribute < $objItem2->$attribute ? -1 : 1;
                });

                return $arrItems;
            };

        }

        if ('desc' === $direction) {
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

    /**
     * @param IsotopeProduct        $product
     * @param ProductCollectionItem $item
     * @param int                   $quantity
     */
    private function setProductForItem(IsotopeProduct $product, ProductCollectionItem $item, $quantity)
    {
        $item->tstamp         = time();
        $item->type           = array_search(\get_class($product), Product::getModelTypes(), true);
        $item->product_id     = (int) $product->getId();
        $item->sku            = (string) $product->getSku();
        $item->name           = (string) $product->getName();
        $item->configuration  = $product->getOptions();
        $item->quantity       = (int) $quantity;
        $item->price          = (float) ($product->getPrice($this) ? $product->getPrice($this)->getAmount((int) $quantity) : 0);
        $item->tax_free_price = (float) ($product->getPrice($this) ? $product->getPrice($this)->getNetAmount((int) $quantity) : 0);
    }

    /**
     * Check if product collection has tax
     *
     * @return bool
     */
    public function hasTax()
    {
        foreach ($this->getSurcharges() as $surcharge) {
            if ($surcharge instanceof Tax) {
                return true;
            }
        }

        return false;
    }
}
