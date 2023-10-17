<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

use Contao\MemberModel;
use Contao\Template;
use Haste\Units\Mass\Scale;
use Isotope\Model\Config;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionSurcharge;


/**
 * IsotopeProductCollection interface defines an Isotope product collection
 */
interface IsotopeProductCollection
{
    /**
     * Returns the ID of the product collection.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns an unguessable unique string to represent this collection (e.g. for secure URLs).
     *
     * @return string
     */
    public function getUniqueId();

    /**
     * Gets the member association with this collection, or null if it belongs to a guest.
     *
     * @return MemberModel|null
     */
    public function getMember();

    /**
     * Returns the store ID.
     * The store ID is used to share or separate collections (e.g. the cart) across multiple shops or root pages.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Returns the shop config associated with this collection.
     *
     * @return Config|null
     */
    public function getConfig();

    /**
     * Return true if collection is locked
     *
     * @return bool
     */
    public function isLocked();

    /**
     * Returns timestamp when the collection was locked
     *
     * @return int|null
     */
    public function getLockTime();

    /**
     * Return true if collection has no items
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Return number of items in the collection
     *
     * @return int
     */
    public function countItems();

    /**
     * Return summary of item quantity in collection
     *
     * @return int
     */
    public function sumItemsQuantity();

    /**
     * Delete all products in the collection
     */
    public function purge();

    /**
     * Lock collection from being modified
     */
    public function lock();

    /**
     * Save changes to the collection.
     */
    public function save();

    /**
     * Sum price of all items in the collection
     *
     * @return float
     */
    public function getSubtotal();

    /**
     * Sum total tax free price of all items in the collection
     *
     * @return float
     */
    public function getTaxFreeSubtotal();

    /**
     * Sum total price of items and surcharges
     *
     * @return float
     */
    public function getTotal();

    /**
     * Sum tax free total of items and surcharges
     *
     * @return float
     */
    public function getTaxFreeTotal();

    /**
     * Returns the ISO 4217 3-character currency code
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Return the item with the latest timestamp (e.g. the latest added item)
     *
     * @return ProductCollectionItem|null
     */
    public function getLatestItem();

    /**
     * Return all items in the collection
     *
     * @param callable
     * @param bool
     *
     * @return \Isotope\Model\ProductCollectionItem[]
     */
    public function getItems($varCallable = null, $blnNoCache = false);

    /**
     * Search item for a specific product
     *
     * @param IsotopeProduct $objProduct
     *
     * @return ProductCollectionItem|null
     */
    public function getItemForProduct(IsotopeProduct $objProduct);

    /**
     * Check if a given product is already in the collection
     *
     * @param IsotopeProduct $objProduct
     * @param bool           $blnIdentical
     *
     * @return bool
     */
    public function hasProduct(IsotopeProduct $objProduct, $blnIdentical = true);

    /**
     * Add a product to the collection
     *
     * @param IsotopeProduct $objProduct
     * @param integer        $intQuantity
     * @param array          $arrConfig
     *
     * @return ProductCollectionItem
     */
    public function addProduct(IsotopeProduct $objProduct, $intQuantity, array $arrConfig = []);

    /**
     * Update a product collection item
     *
     * @param ProductCollectionItem $objItem The product object
     * @param array                 $arrSet  The property(ies) to adjust
     *
     * @return bool
     */
    public function updateItem(ProductCollectionItem $objItem, $arrSet);

    /**
     * Update product collection item with given ID
     *
     * @param int   $intId
     * @param array $arrSet
     *
     * @return bool
     */
    public function updateItemById($intId, $arrSet);

    /**
     * Remove item from collection
     *
     * @param ProductCollectionItem $objItem
     *
     * @return bool
     */
    public function deleteItem(ProductCollectionItem $objItem);

    /**
     * Remove item with given ID from collection
     *
     * @param int $intId
     *
     * @return bool
     */
    public function deleteItemById($intId);

    /**
     * Find surcharges for the current collection
     *
     * @return ProductCollectionSurcharge[]
     */
    public function getSurcharges();

    /**
     * Add all products in the collection to the given scale
     *
     * @param Scale $objScale
     *
     * @return Scale
     */
    public function addToScale(Scale $objScale = null);

    /**
     * Add the collection to a template
     *
     * @param Template $objTemplate
     * @param array     $arrConfig
     */
    public function addToTemplate(Template $objTemplate, array $arrConfig = []);

    /**
     * Add an error message
     *
     * @param string
     */
    public function addError($message);

    /**
     * Check if collection or any item has errors
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * Return the errors array
     *
     * @return array
     */
    public function getErrors();
}
