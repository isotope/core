<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;
use Isotope\CoreBundle\Util\ClosureTrait;

/**
 * @property int         $id
 * @property int         $pid
 * @property int         $tstamp
 * @property string      $language
 * @property int         $dateAdded
 * @property int         $type
 * @property array       $inherit
 * @property bool        $fallback
 * @property string      $alias
 * @property string|null $sku
 * @property string      $name
 * @property string      $teaser
 * @property string      $description
 * @property string      $meta_title
 * @property string      $meta_description
 * @property string      $price
 * @property string      $prices
 * @property string      $baseprice
 * @property string      $shipping_weight
 * @property bool        $shipping_exempt
 * @property bool        $shipping_pickup
 * @property float       $shipping_price
 * @property array       $images
 * @property bool        $protected
 * @property array       $groups
 * @property array       $cssID
 * @property bool        $published
 * @property string      $start
 * @property string      $stop
 */
class CoreProduct extends Product
{
    use ClosureTrait;

    private bool $isVariant = false;

    private array $variantIds = [];

    private \Closure|ProductCollection|null $variants = null;

    private \Closure|CoreProduct|null $defaultVariant = null;

    private \Closure|PriceDefinitionCollection|null $prices = null;

    private CalculatedPrice|\Closure|null $price = null;

    private \Closure|bool $available = true;

    public function __construct(
        array $data,
        private readonly ProductTypeModel $productType,
        private readonly ProductRendererInterface $renderer,
    ) {
        parent::__construct('tl_iso_product.'.$data['id'], $data);
    }

    public function getProductType(): ProductTypeModel
    {
        return $this->productType;
    }

    public function getRenderer(): ProductRendererInterface
    {
        return $this->renderer;
    }

    public function getProductId(): int
    {
        return $this->pid ?: $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string|null
    {
        return $this->sku;
    }

    public function getAlias(): string
    {
        return $this->alias ?: (string) $this->getProductId();
    }

    public function hasVariants(): bool
    {
        return $this->productType->hasVariants();
    }

    public function setIsVariant(bool $isVariant): self
    {
        $this->isVariant = $isVariant;

        return $this;
    }

    public function isVariant(): bool
    {
        return $this->isVariant;
    }

    public function setVariantIds(array $variantIds): self
    {
        $this->variantIds = $variantIds;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getVariantIds(): array
    {
        return $this->variantIds;
    }

    public function getVariants(): \Closure|ProductCollection|null
    {
        $this->resolveClosure($this->variants);

        return $this->variants;
    }

    public function setVariants(\Closure|ProductCollection|null $variants): self
    {
        $this->variants = $variants;

        return $this;
    }

    public function getDefaultVariant(): self|null
    {
        $this->resolveClosure($this->defaultVariant);

        return $this->defaultVariant;
    }

    public function setDefaultVariant(\Closure|self|null $defaultVariant): self
    {
        $this->defaultVariant = $defaultVariant;

        return $this;
    }

    public function getPrices(): PriceDefinitionCollection|null
    {
        $this->resolveClosure($this->prices);

        return $this->prices;
    }

    public function setPrices(\Closure|PriceDefinitionCollection|null $prices): self
    {
        $this->prices = $prices;

        return $this;
    }

    public function getPrice(): CalculatedPrice|null
    {
        $this->resolveClosure($this->price);

        return $this->price;
    }

    public function setPrice(CalculatedPrice|\Closure|null $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isAvailable(): bool
    {
        $this->resolveClosure($this->available);

        return $this->available;
    }

    public function setAvailable(\Closure|bool $available): self
    {
        $this->available = $available;

        return $this;
    }
}
