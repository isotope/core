<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\CoreBundle\Store\Weight\Weight;

abstract class AbstractShipping extends AbstractCheckoutMethod implements ShippingInterface
{
    protected Weight|null $minWeight = null;

    protected Weight|null $maxWeight = null;

    protected Weight|null $shippingWeight = null;

    protected string|null $trackingUrl = null;

    public function setMinMaxWeight(Weight|null $minWeight, Weight|null $maxWeight): static
    {
        $this->minWeight = $minWeight;
        $this->maxWeight = $maxWeight;

        return $this;
    }

    public function setShippingWeight(Weight|null $shippingWeight): static
    {
        $this->shippingWeight = $shippingWeight;

        return $this;
    }

    public function getWeight(): Weight|null
    {
        return $this->shippingWeight;
    }

    public function setTrackingUrl(string|null $trackingUrl): static
    {
        $this->trackingUrl = $trackingUrl;

        return $this;
    }

    public function getTrackingUrl(): string|null
    {
        return $this->trackingUrl;
    }

    #[\Override]
    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }

        $items = $this->getStoreContext()->getCart()?->getLineItems()->products();

        if (
            (($this->minWeight || $this->maxWeight) && !$items)
            || ($this->minWeight && $items->calculateWeight($this->minWeight->getUnit())->getAmount() < $this->minWeight->getAmount())
            || ($this->maxWeight && $items->calculateWeight($this->maxWeight->getUnit())->getAmount() > $this->maxWeight->getAmount())
        ) {
            return false;
        }

        return true;
    }
}
