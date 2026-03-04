<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Twig\TemplateMarkup;

#[AsShipping]
class ProductPriceShipping extends AbstractShipping
{
    private const ATTRIBUTE_NAME = 'shipping_price';

    public function __construct(
        int $id,
        StoreContext $context,
        private readonly ProductCalculation $calculation,
        private readonly int $taxClass,
    ) {
        parent::__construct($id, $context);
    }

    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }

        $cart = $this->getStoreContext()->getCart();

        foreach ($cart->getLineItems() as $item) {
            //if ($item->hasProduct() && $this->hasShippingPrice($item->getProduct())) {
            //    return true;
            //}
        }

        return false;
    }

    public function getPriceDefinition(): PriceDefinition|null
    {
        $isFirst = true;
        $price = 0;

        foreach ($this->getStoreContext()->getCart()->getLineItems()->products() as $item) {
            $cartProduct = $item->getProduct();

            if (null === $cartProduct || !$this->hasShippingPrice($cartProduct)) {
                continue;
            }

            $shippingPrice = $cartProduct->{self::ATTRIBUTE_NAME};

            switch ($this->calculation) {
                case ProductCalculation::sumProducts:
                    $price += $shippingPrice;
                    break;

                case ProductCalculation::lowestProduct:
                    if ($isFirst || $shippingPrice < $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case ProductCalculation::highestProduct:
                    if ($isFirst || $shippingPrice > $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case ProductCalculation::sumItems:
                    $price += $item->getQuantity() * $shippingPrice;
                    break;

                case ProductCalculation::lowestItem:
                    $shippingPrice = $item->getQuantity() * $shippingPrice;

                    if ($isFirst || $shippingPrice < $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case ProductCalculation::highestItem:
                    $shippingPrice = $item->getQuantity() * $shippingPrice;

                    if ($isFirst || $shippingPrice >= $price) {
                        $price = $shippingPrice;
                    }
                    break;
            }

            $isFirst = false;
        }

        return new PriceDefinition($price, $this->taxClass);
    }

    public function hasError(): bool
    {
        return false;
    }

    public function review(): TemplateMarkup|null
    {
        return null;
    }

    private function hasShippingPrice(Product $product): bool
    {
        if (!$product instanceof CoreProduct) {
            return false;
        }

        $attributes = array_merge($product->getProductType()->getAttributes(), $product->getProductType()->getVariantAttributes());

        return \in_array(self::ATTRIBUTE_NAME, $attributes, true)
            && !$product->getExtension(ShippingExtension::class)?->isExemptFromShipping();
    }
}
