<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;

#[AsShipping]
class ProductPriceShipping extends AbstractShipping
{
    private const ATTRIBUTE_NAME = 'shipping_price';

    public function __construct(
        int $id,
        private readonly ProductCalculation $calculation,
        TokenChecker $tokenChecker,
        RuleMatcher $ruleMatcher,
    ) {
        parent::__construct($id, $tokenChecker, $ruleMatcher);
    }

    public function isAvailable(StoreContext $context): bool
    {
        if (!parent::isAvailable($context)) {
            return false;
        }

        $cart = $context->getCart();

        foreach ($cart->getLineItems() as $item) {
            //if ($item->hasProduct() && $this->hasShippingPrice($item->getProduct())) {
            //    return true;
            //}
        }

        return false;
    }

    public function getPrice(IsotopeProductCollection $collection): float|null
    {
        $isFirst = true;
        $price = 0;

        foreach ($collection->getItems() as $item) {
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
                    $price += $item->quantity * $shippingPrice;
                    break;

                case ProductCalculation::lowestItem:
                    $shippingPrice = $item->quantity * $shippingPrice;

                    if ($isFirst || $shippingPrice < $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case ProductCalculation::highestItem:
                    $shippingPrice = $item->quantity * $shippingPrice;

                    if ($isFirst || $shippingPrice >= $price) {
                        $price = $shippingPrice;
                    }
                    break;
            }

            $isFirst = false;
        }

        return Isotope::calculatePrice($price, $this, 'price', $this->taxClass);
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
