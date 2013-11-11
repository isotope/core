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

namespace Isotope\Interfaces;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\ProductCollectionItem;


/**
 * IsotopeProductCollection interface defines an Isotope product collection
 */
interface IsotopeProductCollection
{

    /**
     * Return true if collection is locked
     * @return bool
     */
    public function isLocked();

    /**
     * Return true if collection has no items
     * @return bool
     */
    public function isEmpty();

    /**
     * Return number of items in the collection
     * @return  int
     */
    public function countItems();

    /**
     * Return summary of item quantity in collection
     * @return  int
     */
    public function sumItemsQuantity();

    /**
     * Delete all products in the collection
     */
    public function purge();

    /**
     * Lock collection from begin modified
     */
    public function lock();

    /**
     * Sum price of all items in the collection
     * @return  float
     */
    public function getSubtotal();

    /**
     * Sum total tax free price of all items in the collection
     * @return  float
     */
    public function getTaxFreeSubtotal();

    /**
     * Sum total price of items and surcharges
     * @return  float
     */
    public function getTotal();

    /**
     * Sum tax free total of items and surcharges
     * @return  float
     */
    public function getTaxFreeTotal();

    /**
     * Return the item with the latest timestamp (e.g. the latest added item)
     * @return ProductCollectionItem|null
     */
    public function getLatestItem();

    /**
     * Return all items in the collection
     * @param  callable
     * @param  bool
     * @return array
     */
    public function getItems($varCallable=null, $blnNoCache=false);

    /**
     * Search item for a specific product
     * @param  IsotopeProduct
     * @return ProductCollectionItem|null
     */
    public function getItemForProduct(IsotopeProduct $objProduct);

    /**
     * Check if a given product is already in the collection
     * @param  IsotopeProduct
     * @param  bool
     * @return bool
     */
    public function hasProduct(IsotopeProduct $objProduct, $blnIdentical=true);

    /**
     * Add a product to the collection
     * @param   object
     * @param   integer
     * @param   array
     * @return  ProductCollectionItem
     */
    public function addProduct(IsotopeProduct $objProduct, $intQuantity, array $arrConfig=array());

    /**
     * Update a product collection item
     * @param   object  The product object
     * @param   array   The property(ies) to adjust
     * @return  bool
     */
    public function updateItem(ProductCollectionItem $objItem, $arrSet);

    /**
     * Update product collection item with given ID
     * @param   int
     * @param   array
     * @return  bool
     */
    public function updateItemById($intId, $arrSet);

    /**
     * Remove item from collection
     * @param   ProductCollectionItem
     * @return  bool
     */
    public function deleteItem(ProductCollectionItem $objItem);

    /**
     * Remove item with given ID from collection
     * @param   int
     * @return  bool
     */
    public function deleteItemById($intId);

    /**
     * Find surcharges for the current collection
     * @return  array
     */
    public function getSurcharges();
}
