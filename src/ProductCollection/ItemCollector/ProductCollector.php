<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemCollector;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AutoconfigureTag('isotope.item_collector', ['priority' => 100])]
class ProductCollector implements ItemCollectorInterface
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function process(Cart $cart, Cart $original, StoreContext $context): void
    {
        $items = $original->getLineItems()->whereType(LineItem::TYPE_PRODUCT);

        foreach ($items as $item) {
            $product = $item->getProduct();

            if (!$product instanceof CoreProduct) {
                continue;
            }

            $price = $this->priceCalculator->calculateProduct($product, $item->getQuantity(), false, $context);

            $event = new ProductPriceEvent($product, $context, $item->getConfiguration() ?? [], $price);
            $this->eventDispatcher->dispatch($event);

            $item->setPrice($event->getPrice());

            $cart->add($item);
        }
    }
}
