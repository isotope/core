<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Event;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Adjust source prices before variant/tier selection, currency conversion and tax calculation.
 *
 * References and quantities identify variants and tiers. Preserve them when replacing definitions.
 */
class ProductPriceDefinitionsEvent extends Event
{
    public function __construct(
        private readonly CoreProduct $product,
        private readonly StoreContext $context,
        private PriceDefinitionCollection|null $prices,
        private readonly int $quantity,
        private readonly bool $showRange,
        private readonly array $configuration = [],
    ) {
    }

    public function getProduct(): CoreProduct
    {
        return $this->product;
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public function getPrices(): PriceDefinitionCollection|null
    {
        return $this->prices;
    }

    public function setPrices(PriceDefinitionCollection|null $prices): void
    {
        $this->prices = $prices;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function isRange(): bool
    {
        return $this->showRange;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
