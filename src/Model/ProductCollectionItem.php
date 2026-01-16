<?php

namespace Isotope\CoreBundle\Model;

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollectionDownload;


/**
 * ProductCollectionItem represents an item in a product collection.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $type
 * @property string $identifier
 * @property mixed  $configuration
 * @property string $sku
 * @property string $name
 * @property int    $quantity
 * @property int    $level
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
     * @return Collection<self>|null
     */
    public static function findByCollection(int $pid, array $options = []): Collection|null
    {
        return self::findBy('pid', $pid, $options);
    }

    /**
     * @return self|null
     */
    public static function findByOneCollection(int $pid, string $identifier, array|null $configuration, array $options = []): self|null
    {
        return self::findOneBy(
            ['pid=?', 'identifier=?', 'configuration=?'],
            [$pid, $identifier, serialize($configuration)],
            $options,
        );
    }















    /**
     * Cache the current product
     */
    protected Product|null|false $objProduct = false;

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

    public function getCollection(): IsotopeProductCollection
    {
        return $this->getRelated('pid');
    }

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

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $product = $this->getProduct();

        if (!$product || !$storeManager->runWithCollection($this->getCollection(), static fn (StoreContext $context) => $product->isAvailable())) {
            return false;
        }

        if (!$product->hasVariants()) {
            return true;
        }

        $arrConfig = $this->getConfiguration();
        foreach ($product->getVariantConfig() as $k => $v) {
            if (
                !isset($arrConfig[$k])
                || (
                    $arrConfig[$k] !== $v
                    && (!\is_scalar($arrConfig[$k]) || !\is_scalar($v) || (string) $arrConfig[$k] !== (string) $v)
                )
            ) {
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
     */
    public function getProduct($blnNoCache = false): Product|null
    {
        if (false === $this->objProduct || true === $blnNoCache) {
            $this->objProduct = null;

            /** @var StoreManager $storeManager */
            $storeManager = System::getContainer()->get(StoreManager::class);

            try {
                $storeManager->runWithCollection($this->getCollection(), function (StoreContext $context) {
                    /** @var ProductBuilder $productBuilder */
                    $productBuilder = System::getContainer()->get(ProductBuilder::class);

                    $this->objProduct = $productBuilder->buildOne(new ProductDto($this->identifier, (int) $this->quantity), $context);
                });
            } catch (\Exception) {
                $this->objProduct = null;
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

    public function isProduct(CoreProduct $product): bool
    {
        return $this->identifier === $product->getIdentifier();
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
     */
    public function getConfiguration(): array
    {
        $configuration = StringUtil::deserialize($this->configuration);

        return \is_array($configuration) ? $configuration : [];
    }

    /**
     * Get product price. Automatically falls back to the collection item table if product is not found.
     *
     * @return float|null
     */
    public function getPrice()
    {
        if ($this->isLocked() || !$this->hasProduct()) {
            return $this->price;
        }

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        $objPrice = $storeManager->runWithCollection($this->getCollection(), fn (StoreContext $context) => $this->getProduct()->getPrice($context));

        if (null === $objPrice) {
            return null;
        }

        return $objPrice->getAmount((int) $this->quantity, $this->getConfiguration());
    }

    /**
     * Get tax free product price. Automatically falls back to the collection item table if product is not found.
     *
     * @return float|null
     */
    public function getTaxFreePrice()
    {
        if ($this->isLocked() || !$this->hasProduct()) {
            return $this->tax_free_price;
        }

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return $storeManager->runWithCollection($this->getCollection(), function (StoreContext $context) {
            $objPrice = $this->getProduct()->getPrice($context);

            if (null === $objPrice) {
                return null;
            }

            return $objPrice->getNetAmount((int) $this->quantity, $this->getConfiguration());
        });
    }

    /**
     * Get original product price. Automatically falls back to the collection item table if product is not found.
     *
     * @return float|null
     */
    public function getOriginalPrice()
    {
        if ($this->isLocked() || !$this->hasProduct()) {
            return $this->price;
        }

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return $storeManager->runWithCollection($this->getCollection(), function (StoreContext $context) {
            $objPrice = $this->getProduct()->getPrice($context);

            if (null === $objPrice) {
                return null;
            }

            return $objPrice->getOriginalAmount((int) $this->quantity, $this->getConfiguration());
        });
    }

    /**
     * Get product price multiplied by the requested product quantity
     *
     * @return float|null
     */
    public function getTotalPrice()
    {
        if (null === ($price = $this->getPrice())) {
            return null;
        }

        return ($price * (int) $this->quantity);
    }

    /**
     * Get original product price multiplied by the requested product quantity
     *
     * @return float|null
     */
    public function getTotalOriginalPrice()
    {
        if (null === ($price = $this->getOriginalPrice())) {
            return null;
        }

        return ($price * (int) $this->quantity);
    }

    /**
     * Get tax free product price multiplied by the requested product quantity
     *
     * @return float|null
     */
    public function getTaxFreeTotalPrice()
    {
        if (null === ($price = $this->getTaxFreePrice())) {
            return null;
        }

        return ($price * (int) $this->quantity);
    }

    /**
     * Return downloads associated with this product collection item
     *
     * @return ProductCollectionDownload[]
     */
    public function getDownloads()
    {
        if (null === $this->arrDownloads) {
            $this->arrDownloads = [];

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
     */
    public function hasErrors(): bool
    {
        return 0 !== \count($this->getErrors());
    }

    public function getErrors(): array
    {
        $errors = $this->arrErrors;

        if (!$this->isLocked() && null === $this->getProduct()) {
            $errors[] = $GLOBALS['TL_LANG']['ERR']['collectionItemNotAvailable'];
        }

        return $errors;
    }
}
