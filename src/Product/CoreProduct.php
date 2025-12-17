<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\Price\PriceCollection;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;

/**
 * @property int $id
 * @property int $pid
 * @property int $tstamp
 * @property string $language
 * @property int $dateAdded
 * @property int $type
 * @property array $inherit
 * @property bool $fallback
 * @property string $alias
 * @property string $sku
 * @property string $name
 * @property string $teaser
 * @property string $description
 * @property string $meta_title
 * @property string $meta_description
 * @property string $price
 * @property string $prices
 * @property bool $shipping_exempt
 * @property array $images
 * @property bool $protected
 * @property array $groups
 * @property array $cssID
 * @property bool $published
 * @property string $start
 * @property string $stop
 */
class CoreProduct extends Product
{
    private bool $isVariant = false;
    private array $variantIds = [];
    private ProductCollection|\Closure|null $variants = null;
    private CoreProduct|\Closure|null $defaultVariant = null;
    private PriceCollection|\Closure|null $prices = null;
    private CalculatedPrice|\Closure|null $price = null;
    private bool|\Closure $available = true;

    public function __construct(
        array $data,
        private readonly ProductType $productType,
        private readonly ProductRendererInterface $renderer,
    ) {
        parent::__construct('tl_iso_product.'.$data['id'], $data);
    }

    public function getProductType(): ProductType
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

    public function getSku(): string
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

    public function getVariants(): ProductCollection|\Closure|null
    {
        $this->resolveClosure($this->variants);

        return $this->variants;
    }

    public function setVariants(ProductCollection|\Closure|null $variants): CoreProduct
    {
        $this->variants = $variants;
        return $this;
    }

    public function getDefaultVariant(): CoreProduct|null
    {
        $this->resolveClosure($this->defaultVariant);

        return $this->defaultVariant;
    }

    public function setDefaultVariant(CoreProduct|\Closure|null $defaultVariant): CoreProduct
    {
        $this->defaultVariant = $defaultVariant;

        return $this;
    }

    public function getPrices(): PriceCollection|null
    {
        $this->resolveClosure($this->prices);

        return $this->prices;
    }

    public function setPrices(PriceCollection|\Closure|null $prices): CoreProduct
    {
        $this->prices = $prices;

        return $this;
    }

    public function getPrice(): CalculatedPrice|null
    {
        $this->resolveClosure($this->price);

        return $this->price;
    }

    public function setPrice(CalculatedPrice|\Closure|null $price): CoreProduct
    {
        $this->price = $price;

        return $this;
    }

    public function isAvailable(): bool
    {
        $this->resolveClosure($this->available);

        return $this->available;
    }

    public function setAvailable(bool|\Closure $available): CoreProduct
    {
        $this->available = $available;

        return $this;
    }
}
