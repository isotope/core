<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemCollector;

use Isotope\CoreBundle\Checkout\Shipping\AbstractShipping;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('isotope.item_collector')]
class ShippingCollector implements ItemCollectorInterface
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {
    }

    public function process(Cart $cart, Cart $original, StoreContext $context): void
    {
        $shipping = $original->getShippingMethod();

        if (!$shipping || !$shipping->isAvailable($context)) {
            return;
        }

        $priceDefinition = $shipping->getPriceDefinition();

        if (null === $priceDefinition) {
            return;
        }

        $price = $this->priceCalculator->calculatePriceDefinition($priceDefinition, $original->getSubtotal($shipping->getLevel() - 1), $context);

        $item = new LineItem(LineItem::TYPE_SHIPPING, 'tl_iso_shipping.'.$shipping->getId());
        $item->setLabel($shipping->getLabel());
        $item->setLevel($shipping->getLevel());
        $item->setDynamic(true);
        $item->setPrice($price);

        // TODO: do not check for abstract class
        if ($shipping instanceof AbstractShipping) {
            $item->setWeight($shipping->getWeight());
        }

        $cart->add($item);
    }
}
