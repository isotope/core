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

use Haste\Data\Plain;
use Haste\Haste;
use Haste\Util\Format;


/**
 * ProductCollectionItem represents an item in a product collection.
 *
 * @property int    id
 * @property int    pid
 * @property int    tstamp
 * @property int    product_id
 * @property string type
 * @property string sku
 * @property string name
 * @property mixed  configuration
 * @property int    quantity
 * @property float  price
 * @property float  tax_free_price
 * @property string tax_id
 * @property int    jumpTo
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
     * @var \Isotope\Interfaces\IsotopeProduct|false
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
    protected $arrErrors = array();

    /**
     * True if product collection is locked
     * @var bool
     */
    protected $blnLocked = false;


    /**
     * Check if collection item is available
     * @return  bool
     */
    public function isAvailable()
    {
        if ($this->isLocked()) {
            return true;
        }

        if (!$this->hasProduct() || !$this->getProduct()->isAvailableForCollection($this->getRelated('pid'))) {
            return false;
        }

        $arrConfig = $this->getConfiguration();
        // @todo change to ->getConfiguration() in Isotope 3.0
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
     * Get the product related to this item
     *
     * @param bool $blnNoCache
     *
     * @return  \Isotope\Interfaces\IsotopeProduct|null
     */
    public function getProduct($blnNoCache = false)
    {
        if (false === $this->objProduct || true === $blnNoCache) {

            $this->objProduct = null;

            /** @var \Isotope\Model\Product $strClass */
            $strClass = Product::getClassForModelType($this->type);

            if ($strClass == '' || !class_exists($strClass)) {
                \System::log('Error creating product object of type "' . $this->type . '"', __METHOD__, TL_ERROR);

                return null;
            }

            $this->objProduct = $strClass::findByPk($this->product_id);
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
     * Get product options
     * @return  array
     * @deprecated use getConfiguration
     */
    public function getOptions()
    {
        $arrConfig = deserialize($this->configuration);

        return is_array($arrConfig) ? $arrConfig : array();
    }

    /**
     * Get product configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        $arrConfig = deserialize($this->configuration);

        if (!is_array($arrConfig)) {
            return array();
        }

        if ($this->hasProduct()) {
            Product::setActive($this->getProduct());
        }

        foreach ($arrConfig as $k => $v) {
            $arrConfig[$k] = new Plain($v, $k);

            if ($this->hasProduct()) {
                $arrConfig[$k]['label'] = Format::dcaLabel($this->getProduct()->getTable(), $k);
                $arrConfig[$k]['formatted'] = Haste::getInstance()->call(
                    'replaceInsertTags',
                    Format::dcaValue($this->getProduct()->getTable(), $k, $v)
                );
            }
        }

        if ($this->hasProduct()) {
            Product::unsetActive();
        }

        return $arrConfig;
    }

    /**
     * Get product price. Automatically falls back to the collection item table if product is not found.
     * @return  string
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
     * @return  string
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
     * Get product price multiplied by the requested product quantity
     * @return  string
     */
    public function getTotalPrice()
    {
        return (string) ($this->getPrice() * (int) $this->quantity);
    }

    /**
     * Get tax free product price multiplied by the requested product quantity
     * @return  string
     */
    public function getTaxFreeTotalPrice()
    {
        return (string) ($this->getTaxFreePrice() * (int) $this->quantity);
    }


    /**
     * Return downloads associated with this product collection item
     * @return  ProductCollectionDownload[]
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
     * @param   int
     * @return  bool
     */
    public function increaseQuantityBy($intQuantity)
    {
        $time = time();

        \Database::getInstance()->query("UPDATE " . static::$strTable . " SET tstamp=$time, quantity=(quantity+" . (int) $intQuantity . ") WHERE " . static::$strPk . "=" . $this->{static::$strPk});

        $this->tstamp   = $time;
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
            throw new \UnderflowException('Quantity of product collection item cannot be less than 1.');
        }

        $time = time();

        \Database::getInstance()->query("UPDATE " . static::$strTable . " SET tstamp=$time, quantity=(quantity-" . (int) $intQuantity . ") WHERE " . static::$strPk . "=" . $this->{static::$strPk});

        $this->tstamp   = $time;
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
    public static function sumBy($strField, $strColumn = null, $varValue = null)
    {
        if (static::$strTable == '') {
            return 0;
        }

        $strQuery = "SELECT SUM(" . $strField . ") AS sum FROM " . static::$strTable;

        if ($strColumn !== null) {
            $strQuery .= " WHERE " . (is_array($strColumn) ? implode(" AND ", $strColumn) : static::$strTable . '.' . $strColumn . "=?");
        }

        return (int) \Database::getInstance()->prepare($strQuery)->execute($varValue)->sum;
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
     * Return true if the collection item has errors
     * @return  bool
     */
    public function hasErrors()
    {
        return !empty($this->arrErrors);
    }

    /**
     * Return the errors array
     * @return  array
     */
    public function getErrors()
    {
        return $this->arrErrors;
    }
}
