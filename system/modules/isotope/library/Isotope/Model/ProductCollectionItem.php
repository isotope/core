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


/**
 * ProductCollectionItem represents an item in a product collection.
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductCollectionItem extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_item';

    /**
     * Cache the current product
     * @var IsotopeProduct|false
     */
    protected $objProduct;

    /**
     * True if product collection is locked
     * @var bool
     */
    protected $blnLocked = false;


    /**
     * Return true if product collection item is locked
     */
    public function isLocked()
    {
        return $this->blnLocked;
    }


    /**
     * Lock item, necessary if product collection is locked
     */
    public function lock()
    {
        $this->blnLocked = true;
        $this->objProduct = null;
    }


    /**
     * Get the product related to this item
     * @return IsotopeProduct|null
     */
    public function getProduct($blnNoCache=false)
    {
        if (null === $this->objProduct || true === $blnNoCache) {

            $strClass = $GLOBALS['ISO_PRODUCT'][$this->type]['class'];

            if ($strClass == '' || class_exists($strClass)) {
                $strClass = 'Isotope\Product\Standard';
            }

            $objProductData = \Database::getInstance()->prepare($strClass::getSelectStatement() . " WHERE p1.language='' AND p1.id=?")
                                                      ->execute($this->product_id);

            if ($objProductData->numRows) {
                $this->objProduct = new $strClass($objProductData->row(), deserialize($this->options), $this->blnLocked, $this->quantity);
                $this->objProduct->collection_id = $this->id;
                $this->objProduct->tax_id = $this->tax_id;
                $this->objProduct->reader_jumpTo_Override = $this->href_reader;
            } else {
                $this->objProduct = false;
            }
        }

        return false === $this->objProduct ? null : $this->objProduct;
    }


    /**
     * Return boolean flag if product could be loaded
     * @return bool
     */
    public function hasProduct()
    {
        return (null !== $this->getProduct());
    }


    /**
     * Get product SKU. Automatically falls back to the collection item table if product is not found.
     * @return string;
     */
    public function getSku()
    {
        $objProduct = $this->getProduct();

        return (string) (null === $objProduct || $this->isLocked()) ? $this->sku : $objProduct->sku;
    }


    /**
     * Get product name. Automatically falls back to the collection item table if product is not found.
     * @return string;
     */
    public function getName()
    {
        $objProduct = $this->getProduct();

        return (string) (null === $objProduct || $this->isLocked()) ? $this->name : $objProduct->name;
    }


    /**
     * Get product options. Automatically falls back to the collection item table if product is not found.
     * @return string;
     */
    public function getOptions()
    {
        $objProduct = $this->getProduct();

        return (string) (null === $objProduct || $this->isLocked()) ? deserialize($this->options) : $objProduct->getOptions(true);
    }


    /**
     * Get product price. Automatically falls back to the collection item table if product is not found.
     * @return string;
     */
    public function getPrice()
    {
        $objProduct = $this->getProduct();

        return (string) (null === $objProduct || $this->isLocked()) ? $this->price : $objProduct->price;
    }


    /**
     * Get tax free product price. Automatically falls back to the collection item table if product is not found.
     * @return string;
     */
    public function getTaxFreePrice()
    {
        $objProduct = $this->getProduct();

        return (string) (null === $objProduct || $this->isLocked()) ? $this->tax_free_price : $objProduct->tax_free_price;
    }
}
