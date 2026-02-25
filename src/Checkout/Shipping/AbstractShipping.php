<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\CoreBundle\Store\Weight\Weight;

abstract class AbstractShipping extends AbstractCheckoutMethod implements ShippingInterface
{
    protected float $minWeight = 0;

    protected float $maxWeight = 0;

    protected Weight|null $shippingWeight = null;

    public function setMinMaxWeight(float $minWeight, float $maxWeight): static
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
}
