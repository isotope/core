<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Weight\Weight;
use Isotope\CoreBundle\Twig\TemplateMarkup;

#[AsShipping]
class FlatShipping extends AbstractShipping
{
    /**
     * @var array<int>|null
     */
    private array|null $flatProductTypes = null;

    private Weight|null $flatWeight = null;

    public function __construct(
        int $id,
        StoreContext $context,
        private FlatCalculation|null $flatCalculation,
    ) {
        parent::__construct($id, $context);
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

    public function setFlatWeight(Weight|null $flatWeight): static
    {
        $this->flatWeight = $flatWeight;

        return $this;
    }

    public function getPriceDefinition(): PriceDefinition|null
    {
        $priceDefinition = parent::getPriceDefinition();

        if (!$priceDefinition) {
            return null;
        }

        $multiply = match ($this->flatCalculation) {
            FlatCalculation::perProduct,
            FlatCalculation::perItem => $this->productMultiplier($this->getStoreContext()->getCart()?->getLineItems()->products()),
            FlatCalculation::perWeight => $this->weightMultiplier($this->getStoreContext()->getCart()?->getLineItems()->products()),
            default => 1,
        };

        return $priceDefinition->withAmount($priceDefinition->getAmount() * $multiply);
    }

    public function review(): TemplateMarkup|null
    {
        return null;
    }

    public function hasError(): bool
    {
        return false;
    }

    private function productMultiplier(LineItemCollection|null $items): int
    {
        if (!$items) {
            return 0;
        }

        $multiply = 0;

        foreach ($items as $item) {
            /** @var CoreProduct $product */
            if (!($product = $item->getProduct()) || $product->getExtension(ShippingExtension::class)?->isExemptFromShipping()) {
                continue;
            }

            if (null !== $this->flatProductTypes) {
                $productType = $product->getProductType();

                if (!$productType || !\in_array((int) $productType->id, $this->flatProductTypes, true)) {
                    continue;
                }
            }

            $multiply += FlatCalculation::perProduct === $this->flatCalculation ? 1 : $item->getQuantity();
        }

        return $multiply;
    }

    private function weightMultiplier(LineItemCollection|null $items): float
    {
        if (!$items) {
            return 0;
        }

        $multiply = floor($this->flatWeight->getAmount());

        if ($multiply <= 1) {
            return 1;
        }

        $cartWeight = floor($items->calculateWeight($this->flatWeight->getUnit())->getAmount());

        return $cartWeight / $multiply;
    }
}
