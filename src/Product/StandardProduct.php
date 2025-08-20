<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\System;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProduct;
use Isotope\CoreBundle\ModelType\ProductRegistry;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;

#[AsProduct]
class StandardProduct extends AbstractProduct
{
    public function isAvailableInFrontend()
    {
        return $this->model->isAvailableInFrontend();
    }

    public function isAvailableForCollection(IsotopeProductCollection $objCollection)
    {
        return $this->model->isAvailableForCollection($objCollection);
    }

    public function isExemptFromShipping(): bool
    {
        return $this->model->isExemptFromShipping();
    }

    public function isPickupOnly(): bool
    {
        return $this->model->isPickupOnly();
    }

    public function hasVariants(): bool
    {
        return $this->model->hasVariants();
    }

    public function getVariantIds(): array
    {
        return $this->model->getVariantIds();
    }

    public function isVariant(): bool
    {
        return $this->model->isVariant();
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
        return $this->model->hasVariantPrices();
    }

    public function getVariantConfig(): array
    {
        return $this->model->getVariantConfig();
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
