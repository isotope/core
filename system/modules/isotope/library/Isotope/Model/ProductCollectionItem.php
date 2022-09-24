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

use Contao\Database;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Haste\Data\Plain;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductWithOptions;
use Isotope\Isotope;


/**
 * ProductCollectionItem represents an item in a product collection.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $product_id
 * @property string $type
 * @property string $sku
 * @property string $name
 * @property mixed  $configuration
 * @property int    $quantity
 * @property float  $price
 * @property float  $tax_free_price
 * @property string $tax_id
 * @property int    $jumpTo
*/
class ProductCollectionItem extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_item';

    /**
     * Cache the current product
     * @var IsotopeProduct|IsotopeProductWithOptions|false
     */
    protected $objProduct = false;

    /**
     * Cache downloads for the collection item
     * @var array
     */
    protected $arrDownloads;

    /**
     * Errors
     * @var array
     */
    protected $arrErrors = [];

    /**
     * True if product collection is locked
     * @var bool
     */
    protected $blnLocked = false;


    /**
     * Check if collection item is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        if ($this->isLocked()) {
            return true;
        }

        if (isset($GLOBALS['ISO_HOOKS']['itemIsAvailable']) && \is_array($GLOBALS['ISO_HOOKS']['itemIsAvailable'])) {
            foreach ($GLOBALS['ISO_HOOKS']['itemIsAvailable'] as $callback) {
                $available = System::importStatic($callback[0])->{$callback[1]}($this);

                // If return value is boolean then we accept it as result
                if (true === $available || false === $available) {
                    return $available;
                }
            }
        }

        if (!$this->hasProduct() || !$this->getProduct()->isAvailableForCollection($this->getRelated('pid'))) {
            return false;
        }

        $arrConfig = $this->getOptions();
        foreach ($this->getProduct()->getOptions() as $k => $v) {
            if ($arrConfig[$k] !== $v) {
                return false;
            }
        }

        return true;
    }

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
    }

    /**
     * Delete downloads when deleting product collection item
     *
     * @return int
     */
    public function delete()
    {
        $intId = $this->id;
        $intAffected = parent::delete();

        if ($intAffected) {
            Database::getInstance()->query("DELETE FROM tl_iso_product_collection_download WHERE pid=$intId");
        }

        return $intAffected;
    }

    /**
     * Get the product related to this item
     *
     * @param bool $blnNoCache
     *
     * @return IsotopeProduct|null
     */
    public function getProduct($blnNoCache = false)
    {
        if (false === $this->objProduct || true === $blnNoCache) {

            $this->objProduct = null;

            /** @var string|\Isotope\Model\Product $strClass */
            $strClass = Product::getClassForModelType($this->type);

            if ($strClass == '' || !class_exists($strClass)) {
                System::log('Error creating product object of type "' . $this->type . '"', __METHOD__, TL_ERROR);

                return null;
            }

            try {
                $this->objProduct = $strClass::findByPk($this->product_id);
            } catch (\Exception $e) {
                $this->objProduct = null;
                $this->addError($e->getMessage());
            }

            if (null !== $this->objProduct && $this->objProduct instanceof IsotopeProductWithOptions) {
                try {
                    if ($this->objProduct instanceof Model) {
                        $this->objProduct = clone $this->objProduct;
                        $this->objProduct->preventSaving(false);
                        $this->objProduct->id = $this->product_id;
                    }

                    $this->objProduct->setOptions($this->getOptions());
                } catch (\RuntimeException $e) {
                    $this->addError($GLOBALS['TL_LANG']['ERR']['collectionItemNotAvailable']);
                }
            }
        }

        return $this->objProduct;
    }

    /**
     * Return boolean flag if product could be loaded
     *
     * @return bool
     */
    public function hasProduct()
    {
        return (null !== $this->getProduct());
    }

    /**
     * Get product SKU. Automatically falls back to the collection item table if product is not found.
     *
     * @return string
     */
    public function getSku()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? $this->sku : $this->getProduct()->getSku();
    }

    /**
     * Get product name. Automatically falls back to the collection item table if product is not found.
     *
     * @return string
     */
    public function getName()
    {
        return (string) ($this->isLocked() || !$this->hasProduct()) ? $this->name : $this->getProduct()->getName();
    }

    /**
     * Returns key-value array for variant-enabled and customer editable attributes.
     *
     * @return array
     *
     * @deprecated Use getOptions()
     */
    public function getAttributes()
    {
        return $this->getOptions();
    }

    /**
     * Returns key-value array for variant-enabled and customer editable attributes.
     *
     * @return  array
     */
    public function getOptions()
    {
        $arrConfig = StringUtil::deserialize($this->configuration);

        return \is_array($arrConfig) ? $arrConfig : [];
    }

    /**
     * Get product configuration
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0. Use getOptions() instead.
     */
    public function getConfiguration()
    {
        $arrConfig = StringUtil::deserialize($this->configuration);

        if (empty($arrConfig) || !\is_array($arrConfig)) {
            return array();
        }

        if ($this->hasProduct()) {
            return Isotope::formatProductConfiguration($arrConfig, $this->getProduct());

        } else {
            foreach ($arrConfig as $k => $v) {
                $arrConfig[$k] = new Plain($v, $k);
            }

            return $arrConfig;
        }
    }

    /**
     * Get product price. Automatically falls back to the collection item table if product is not found.
     *
     * @return string
     */
    public function getPrice()
    {
        if ($this->isLocked() || !$this->hasProduct()) {
            return $this->price;
        }

        $objPrice = $this->getProduct()->getPrice($this->getRelated('pid'));

        if (null === $objPrice) {
            return '';
        }

        return $objPrice->getAmount((int) $this->quantity, $this->getOptions());
    }

    /**
     * Get tax free product price. Automatically falls back to the collection item table if product is not found.
     *
     * @return string
     */
    public function getTaxFreePrice()
    {
        if ($this->isLocked() || !$this->hasProduct()) {
            return $this->tax_free_price;
        }

        $objPrice = $this->getProduct()->getPrice($this->getRelated('pid'));

        if (null === $objPrice) {
            return '';
        }

        return $objPrice->getNetAmount((int) $this->quantity, $this->getOptions());
    }

    /**
     * Get original product price. Automatically falls back to the collection item table if product is not found.
     *
     * @return string
     */
    public function getOriginalPrice()
    {
        if ($this->isLocked() || !$this->hasProduct()) {
            return $this->price;
        }

        $objPrice = $this->getProduct()->getPrice($this->getRelated('pid'));

        if (null === $objPrice) {
            return '';
        }

        return $objPrice->getOriginalAmount((int) $this->quantity, $this->getOptions());
    }

    /**
     * Get product price multiplied by the requested product quantity
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return (string) ($this->getPrice() * (int) $this->quantity);
    }

    /**
     * Get original product price multiplied by the requested product quantity
     *
     * @return string
     */
    public function getTotalOriginalPrice()
    {
        return (string) ($this->getOriginalPrice() * (int) $this->quantity);
    }

    /**
     * Get tax free product price multiplied by the requested product quantity
     *
     * @return string
     */
    public function getTaxFreeTotalPrice()
    {
        return (string) ($this->getTaxFreePrice() * (int) $this->quantity);
    }

    /**
     * Return downloads associated with this product collection item
     *
     * @return ProductCollectionDownload[]
     */
    public function getDownloads()
    {
        if (null === $this->arrDownloads) {
            $this->arrDownloads = array();

            $objDownloads = ProductCollectionDownload::findBy('pid', $this->id);

            if (null !== $objDownloads) {
                while ($objDownloads->next()) {
                    $this->arrDownloads[] = $objDownloads->current();
                }
            }
        }

        return $this->arrDownloads;
    }

    /**
     * Increase quantity of product collection item
     *
     * @param int $intQuantity
     *
     * @return self
     */
    public function increaseQuantityBy($intQuantity)
    {
        $time = time();

        Database::getInstance()->query("
            UPDATE tl_iso_product_collection_item
            SET tstamp=$time, quantity=(quantity+" . (int) $intQuantity . ')
            WHERE id=' . $this->id
        );

        $this->tstamp   = $time;
        $this->quantity = Database::getInstance()
            ->query("SELECT quantity FROM tl_iso_product_collection_item WHERE id=" . $this->id)
            ->quantity
        ;

        return $this;
    }

    /**
     * Decrease quantity of product collection item
     *
     * @param int $intQuantity
     *
     * @return self
     */
    public function decreaseQuantityBy($intQuantity)
    {
        if (($this->quantity - $intQuantity) < 1) {
            throw new \UnderflowException('Quantity of product collection item cannot be less than 1.');
        }

        $time = time();

        Database::getInstance()->query("
            UPDATE tl_iso_product_collection_item
            SET tstamp=$time, quantity=(quantity-" . (int) $intQuantity . ')
            WHERE id=' . $this->id
        );

        $this->tstamp   = $time;
        $this->quantity = Database::getInstance()
            ->query('SELECT quantity FROM tl_iso_product_collection_item WHERE id=' . $this->id)
            ->quantity
        ;

        return $this;
    }

    /**
     * Calculate the sum of a database column
     *
     * @param string $strField
     * @param mixed  $strColumn
     * @param mixed  $varValue
     *
     * @return int
     */
    public static function sumBy($strField, $strColumn = null, $varValue = null)
    {
        $strQuery = "SELECT SUM($strField) AS sum FROM tl_iso_product_collection_item";

        if ($strColumn !== null) {
            $strQuery .= ' WHERE ' . (\is_array($strColumn) ? implode(' AND ', $strColumn) : static::$strTable . '.' . $strColumn . "=?");
        }

        return (int) Database::getInstance()->prepare($strQuery)->execute($varValue)->sum;
    }

    /**
     * Add an error message
     *
     * @param string $strError
     */
    public function addError($strError)
    {
        $this->arrErrors[] = $strError;
    }

    /**
     * Return true if the collection item has errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return 0 !== \count($this->arrErrors);
    }

    /**
     * Return the errors array
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->arrErrors;
    }
}
