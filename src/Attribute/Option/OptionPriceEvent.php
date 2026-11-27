<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Symfony\Contracts\EventDispatcher\Event;

class OptionPriceEvent extends Event
{
    public function __construct(
        private readonly OptionsAttributeInterface $attribute,
        private readonly Option $option,
        private readonly ProductContext $productContext,
        private readonly PriceDefinition $priceDefinition,
        private readonly CalculatedPrice $productPrice,
        private CalculatedPrice|null $price,
    ) {
    }

    public function getAttribute(): OptionsAttributeInterface
    {
        return $this->attribute;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    public function getProductContext(): ProductContext
    {
        return $this->productContext;
    }

    public function getPriceDefinition(): PriceDefinition
    {
        return $this->priceDefinition;
    }

    public function getProductPrice(): CalculatedPrice
    {
        return $this->productPrice;
    }

    public function getPrice(): CalculatedPrice|null
    {
        return $this->price;
    }

    public function setPrice(CalculatedPrice|null $price): self
    {
        $this->price = $price;

        return $this;
    }
}
