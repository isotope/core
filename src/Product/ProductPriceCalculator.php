<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Product\Event\ProductPriceDefinitionsEvent;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Calculates product prices from source definitions, never from an already adjusted price.
 */
class ProductPriceCalculator
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function calculate(CoreProduct $product, int $quantity, bool $showRange, StoreContext $context, array $configuration = []): CalculatedPrice|null
    {
        $prices = $product->getPrices();

        $definitions = new ProductPriceDefinitionsEvent(
            $product,
            $context,
            null === $prices ? null : new PriceDefinitionCollection(array_map(static fn (PriceDefinition $price) => clone $price, $prices->toArray())),
            $quantity,
            $showRange,
            $configuration,
        );

        $this->eventDispatcher->dispatch($definitions);

        // Keep listener changes local to this calculation, including changes to individual definitions.
        $toCalculate = clone $product;
        $toCalculate->setPrices($definitions->getPrices());

        $price = $this->priceCalculator->calculateProduct($toCalculate, $quantity, $showRange, $context);
        $event = new ProductPriceEvent($product, $context, $configuration, $price, $quantity, $showRange);

        $this->eventDispatcher->dispatch($event);

        return $event->getPrice();
    }
}
