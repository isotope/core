<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\System;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProduct;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\ModelType\ProductRegistry;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;

#[AsProduct]
class StandardProduct extends AbstractProduct
{
    public function __construct(
        Product $model,
        ProductType $productType,
    ) {
        parent::__construct($model, $productType);
    }

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), AttributeRegistry::class];
    }

    public function isExemptFromShipping(): bool
    {
        if ($this['shipping_exempt']) {
            return true;
        }

        return '1' === $this->productType->shipping_exempt;
    }

    public function isPickupOnly(): bool
    {
        if ($this['shipping_pickup']) {
            return true;
        }

        return '2' === $this->productType->shipping_exempt;
    }

    public function hasVariants(): bool
    {
        return $this->productType->hasVariants();
    }

    public function getVariantIds(): array
    {
        return $this->model->getVariantIds();
    }

    public function isVariant(): bool
    {
        return $this['pid'] > 0 && $this->hasVariants();
    }

    public function getMainProduct(): self
    {
        return System::getContainer()->get(ProductRegistry::class)->get(Product::findById($this->getProductId()));
    }

    public function getDefaultVariant(): self|null
    {
        return System::getContainer()->get(ProductRegistry::class)->get(Product::findDefaultVariantOfProduct($this->getProductId(), $this->getVariantIds()));
    }

    public function getVariant(array $configuration): self|null
    {
        return System::getContainer()->get(ProductRegistry::class)->get(Product::findVariantOfProduct($this->getVariantIds(), $configuration));
    }

    public function hasVariantPrices(): bool
    {
        return $this->hasVariants() && \in_array('price', $this->productType->getVariantAttributes(), true);
    }

    public function getVariantConfig(): array
    {
        if (!$this->isVariant()) {
            return array();
        }

        $arrVariantConfig = array();
        $arrAttributes = array_intersect(
            $this->productType->getVariantAttributes(),
            $this->get(AttributeRegistry::class)->getVariantOptionFields()
        );

        foreach (array_unique($arrAttributes) as $attribute) {
            $arrVariantConfig[$attribute] = $this[$attribute];
        }

        return $arrVariantConfig;
    }

    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if (null !== $objCollection && $objCollection !== Isotope::getCart()) {
            return ProductPrice::findByProductAndCollection($this, $objCollection);
        }

        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        return ProductPrice::findByProductAndCollection($this, $objCollection);
    }

    public function getMinimumQuantity()
    {
        return $this->model->getMinimumQuantity();
    }
}
