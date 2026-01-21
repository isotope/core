<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\Model\ProductCollectionItem;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\Haste\Units\Mass\Weighable;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;

#[AsShipping]
class FlatShipping extends AbstractShipping
{
    /**
     * @var array<int>|null
     */
    private array|null $flatProductTypes = null;

    private Weighable|null $flatWeight = null;

    public function __construct(
        int $id,
        private FlatCalculation|null $flatCalculation,
        TokenChecker $tokenChecker,
        RuleMatcher $ruleMatcher,
    ) {
        parent::__construct($id, $tokenChecker, $ruleMatcher);
    }

    public function getFlatCalculation(): FlatCalculation|null
    {
        return $this->flatCalculation;
    }

    public function setFlatCalculation(FlatCalculation|null $flatCalculation): self
    {
        $this->flatCalculation = $flatCalculation;

        return $this;
    }

    public function setFlatProductTypes(array|null $flatProductTypes): static
    {
        $this->flatProductTypes = $flatProductTypes;

        return $this;
    }

    public function setFlatWeight(Weighable|null $flatWeight): static
    {
        $this->flatWeight = $flatWeight;

        return $this;
    }

    public function getPrice(IsotopeProductCollection $collection): float|null
    {
        $price = $this->calculatePrice($collection);

        if (null === $price) {
            return null;
        }

        $multiply = match ($this->flatCalculation) {
            FlatCalculation::perProduct,
            FlatCalculation::perItem => $this->productMultiplier($collection->getItems()),
            FlatCalculation::perWeight => $this->weightMultiplier($collection),
            default => 1,
        };

        $price *= $multiply;

        return Isotope::calculatePrice($price, $this, 'price', $this->taxClass);
    }

    /**
     * @param array<ProductCollectionItem> $items
     */
    private function productMultiplier(array $items): int
    {
        $multiply = 0;

        foreach ($items as $objItem) {
            /** @var CoreProduct $product */
            if (!($product = $objItem->getProduct()) || $product->getExtension(ShippingExtension::class)?->isExemptFromShipping()) {
                continue;
            }

            if (null !== $this->flatProductTypes) {
                $productType = $product->getProductType();

                if (!$productType || !\in_array((int) $productType->id, $this->flatProductTypes, true)) {
                    continue;
                }
            }

            $multiply += FlatCalculation::perProduct === $this->flatCalculation ? 1 : $objItem->quantity;
        }

        return $multiply;
    }

    private function weightMultiplier(IsotopeProductCollection $collection): float
    {
        $multiply = floor($this->flatWeight->getWeightValue());

        if ($multiply <= 1) {
            return 1;
        }

        $cartWeight = floor($collection->addToScale()->amountIn($this->flatWeight->getWeightUnit()));

        return $cartWeight / $multiply;
    }
}
