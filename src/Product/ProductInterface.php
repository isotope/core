<?php

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Model\ProductType;

interface ProductInterface extends CollectibleInterface, VariantProductInterface, \IteratorAggregate, \ArrayAccess, \Countable
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
}
