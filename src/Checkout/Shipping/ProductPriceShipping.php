<?php

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Symfony\Bundle\SecurityBundle\Security;

#[AsShipping]
class ProductPriceShipping extends AbstractShipping
{
    private const ATTRIBUTE_NAME = 'shipping_price';

    public function __construct(
        int $id,
        private readonly ProductCalculation $calculation,
        Security $security,
        TokenChecker $tokenChecker
    ) {
        parent::__construct($id, $security, $tokenChecker);
    }

    public function isAvailable(IsotopeOrderableCollection $collection): bool
    {
        if (!parent::isAvailable($collection)) {
            return false;
        }

        foreach ($collection->getItems() as $item) {
            if ($item->hasProduct() && $this->hasShippingPrice($item->getProduct())) {
                return true;
            }
        }

        return false;
    }

    public function getPrice(IsotopeProductCollection $collection): float|null
    {
        $isFirst = true;
        $price = 0;
        $product = null;

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
                    $price += ($item->quantity * $shippingPrice);
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

    private function hasShippingPrice(IsotopeProduct $product): bool
    {
        $attributes = array_merge($product->getType()->getAttributes(), $product->getType()->getVariantAttributes());

        if (!$product->isExemptFromShipping() && \in_array(self::ATTRIBUTE_NAME, $attributes, true)) {
            return true;
        }

        return false;
    }
}
