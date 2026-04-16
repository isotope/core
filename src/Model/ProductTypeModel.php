<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Controller;
use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $class
 * @property bool   $fallback
 * @property string $description
 * @property bool   $prices
 * @property bool   $show_price_tiers
 * @property string $list_template
 * @property string $reader_template
 * @property int    $list_gallery
 * @property int    $reader_gallery
 * @property string $cssClass
 * @property array  $attributes
 * @property bool   $variants
 * @property array  $variant_attributes
 * @property bool   $force_variant_options
 * @property string $shipping_exempt
 * @property bool   $downloads
 */
class ProductTypeModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_producttype';

    private array|null $attributesCache = null;

    private array|null $variantAttributesCache = null;

    /**
     * Initialize serialized values.
     */
    public function setRow(array $arrData): self
    {
        parent::setRow($arrData);

        $this->attributes = StringUtil::deserialize($this->attributes);
        $this->variant_attributes = StringUtil::deserialize($this->variant_attributes);

        if (!\is_array($this->attributes)) {
            $this->attributes = [];
        }

        if (!\is_array($this->variant_attributes)) {
            $this->variant_attributes = [];
        }

        return $this;
    }

    /**
     * Returns true if variants are enabled in the product type, otherwise returns false.
     */
    public function hasVariants(): bool
    {
        return (bool) $this->variants;
    }

    public function hasVariantPrices(): bool
    {
        return $this->hasVariants() && \in_array('price', $this->getVariantAttributes(), true);
    }

    /**
     * Returns true if advanced prices are enabled in the product type, otherwise returns false.
     */
    public function hasAdvancedPrices(): bool
    {
        return (bool) $this->prices;
    }

    /**
     * Returns true if show price tiers is enabled in the product type, otherwise returns false.
     */
    public function showPriceTiers(): bool
    {
        return $this->prices && $this->show_price_tiers;
    }

    /**
     * Returns true if downloads are enabled in the product type, otherwise returns false.
     */
    public function hasDownloads(): bool
    {
        return (bool) $this->downloads;
    }

    /**
     * Get enabled attributes by sorting.
     *
     * @return list<string>
     */
    public function getAttributes(): array
    {
        if (null === $this->attributesCache) {
            $this->attributesCache = $this->getEnabledAttributesByPosition($this->attributes);
        }

        return $this->attributesCache;
    }

    /**
     * Get enabled variant attributes by sorting.
     *
     * @return list<string>
     */
    public function getVariantAttributes(): array
    {
        if (null === $this->variantAttributesCache) {
            if (!$this->hasVariants()) {
                $this->variantAttributesCache = [];
            } else {
                $this->variantAttributesCache = $this->getEnabledAttributesByPosition($this->variant_attributes);
            }
        }

        return $this->variantAttributesCache;
    }

    /**
     * Get all product types that are in use.
     *
     * @return Collection<ProductTypeModel>|null
     */
    public static function findAllUsed(array $arrOptions = []): Collection|null
    {
        $t = static::$strTable;

        return static::findBy(["$t.id IN (SELECT type FROM tl_iso_product WHERE pid=0)"], null, $arrOptions);
    }

    /**
     * Gets the number of product types with attributes.
     */
    public static function countByVariants(): int
    {
        static $result;

        if (null === $result) {
            $result = Database::getInstance()->query(
                "SELECT COUNT(*) AS total FROM tl_iso_producttype WHERE variants='1'",
            )->total;
        }

        return $result;
    }

    /**
     * Sort the attributes based on their position (from wizard) and return their names only.
     *
     * @return list<string>
     */
    private function getEnabledAttributesByPosition(array $config): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $arrFields = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        $config = array_filter($config, static fn ($a) => (($a['enabled'] ?? false)
            && \is_array($arrFields[$a['name']] ?? null)
            && !empty($arrFields[$a['name']]['attributes']['legend'])
        ));

        uasort($config, static fn ($a, $b): int => $a['position'] <=> $b['position']);

        return array_keys($config);
    }
}
