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
    protected $objProduct = false;

    /**
     * Cache downloads for the collection item
     * @var array
     */
    protected $arrDownloads;

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
        $this->objProduct = false;
    }


    /**
     * Get the product related to this item
     * @return  IsotopeProduct|null
     */
    public function getProduct($blnNoCache=false)
    {
        if (false === $this->objProduct || true === $blnNoCache) {

            $this->objProduct = null;

            $strClass = Product::getClassForModelType($this->type);

            try {
                $this->objProduct = $strClass::findByPk($this->product_id);
                $this->objProduct->setOptions(deserialize($this->options));
                $this->objProduct->setQuantity($this->quantity);
                $this->objProduct->reader_jumpTo_Override = $this->href_reader;

                if ($this->blnLocked) {
                    $this->objProduct->lock();
                }

            } catch (\Exception $e) {
                \System::log("Error creating product object: " . $e->getMessage(), __METHOD__, TL_ERROR);
            }
        }

        return $this->objProduct;
    }


    /**
     * Return boolean flag if product could be loaded
     * @return  bool
     */
    public function hasProduct()
    {
        return (null !== $this->getProduct());
    }


    /**
     * Get product SKU. Automatically falls back to the collection item table if product is not found.
     * @return  string
     */
    public function getSku()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? $this->sku : $this->getProduct()->sku;
    }


    /**
     * Get product name. Automatically falls back to the collection item table if product is not found.
     * @return  string
     */
    public function getName()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? $this->name : $this->getProduct()->name;
    }


    /**
     * Get product options. Automatically falls back to the collection item table if product is not found.
     * @return  string
     */
    public function getOptions()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? deserialize($this->options) : $this->getProduct()->getOptions();
    }


    /**
     * Get product price. Automatically falls back to the collection item table if product is not found.
     * @return  string
     */
    public function getPrice()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? $this->price : $this->getProduct()->getPrice()->getAmount((int) $this->quantity);
    }


    /**
     * Get tax free product price. Automatically falls back to the collection item table if product is not found.
     * @return  string
     */
    public function getTaxFreePrice()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? $this->tax_free_price : $this->getProduct()->getPrice()->getNetAmount((int) $this->quantity);
    }


    /**
     * Return downloads associated with this product collection item
     * @return  array
     */
    public function getDownloads()
    {
        if (null === $this->arrDownloads) {
            $objDownloads = ProductCollectionDownload::findBy('pid', $this->id);

            $this->arrDownloads = (null === $objDownloads) ? array() : $objDownloads->fetchAll();
        }

        return $this->arrDownloads;
    }


    /**
     * Increase quantity of product collection item
     * @param   int
     * @return  bool
     */
    public function increaseQuantityBy($intQuantity)
    {
        $time = time();

        \Database::getInstance()->query("UPDATE " . static::$strTable . " SET tstamp=$time, quantity=(quantity+" . (int) $intQuantity . ") WHERE " . static::$strPk . "=" . $this->{static::$strPk});

        $this->tstamp = $time;
        $this->quantity = \Database::getInstance()->query("SELECT quantity FROM " . static::$strTable . " WHERE " . static::$strPk . "=" . $this->{static::$strPk})->quantity;

        return $this;
    }

    /**
     * Decrease quantity of product collection item
     * @param   int
     * @return  bool
     */
    public function decreaseQuantityBy($intQuantity)
    {
        if (($this->quantity - $intQuantity) < 1) {
            throw new UnderflowException('Quantity of product collection item cannot be less than 1.');
        }

        $time = time();

        \Database::getInstance()->query("UPDATE " . static::$strTable . " SET tstamp=$time, quantity=(quantity-" . (int) $intQuantity . ") WHERE " . static::$strPk . "=" . $this->{static::$strPk});

        $this->tstamp = $time;
        $this->quantity = \Database::getInstance()->query("SELECT quantity FROM " . static::$strTable . " WHERE " . static::$strPk . "=" . $this->{static::$strPk})->quantity;

        return $this;
    }


    /**
     * Calculate the sum of a database column
     * @param   string
     * @param   mixed
     * @param   mixed
     * @return  int
     */
    public static function sumBy($strField, $strColumn=null, $varValue=null)
    {
        if (static::$strTable == '')
		{
			return 0;
		}

		$strQuery = "SELECT SUM(" . $strField . ") AS sum FROM " . static::$strTable;

		if ($strColumn !== null)
		{
			$strQuery .= " WHERE " . (is_array($strColumn) ? implode(" AND ", $strColumn) : static::$strTable . '.' . $strColumn . "=?");
		}

		return (int) \Database::getInstance()->prepare($strQuery)->execute($varValue)->sum;
    }
}
