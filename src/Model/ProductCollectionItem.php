<?php

namespace Isotope\CoreBundle\Model;

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;
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
 * @property string $label
 * @property int    $quantity
 * @property int    $level
 * @property float  $price
 * @property float  $tax_free_price
 * @property string $tax_id
 * @property int    $referencePage
 *
 * @property string|null $unit_label
*/
class ProductCollectionItem extends Model
{
    /**
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
    public static function findOneByCollection(int $pid, string $identifier, array|null $configuration, array $options = []): self|null
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

    public function getCollection(): IsotopeProductCollection
    {
        return $this->getRelated('pid');
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
}
