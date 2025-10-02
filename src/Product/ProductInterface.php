<?php

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;

interface ProductInterface extends CollectibleInterface, \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * Returns the ID for this product or the parent of a variant.
     */
    public function getProductId(): int;

    /**
     * Advertisable products must always have a product type.
     */
    public function getProductType(): ProductType;

    public function getAlias(): string;

    public function getMetaTitle(): string;

    public function getMetaDescription(): string;

    public function getRenderer(): ProductRendererInterface;

    public function hasVariants(): bool;

    /**
     * @return array<int>
     */
    public function getVariantIds(): array;

    public function isVariant(): bool;

    public function getMainProduct(): self;

    public function getDefaultVariant(): self|null;

    public function getVariant(array $configuration): self|null;

    public function hasVariantPrices(): bool;

    /**
     * Get variant option field values
     */
    public function getVariantConfig(): array;
}
