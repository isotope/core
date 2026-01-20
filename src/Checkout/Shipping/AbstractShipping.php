<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\Haste\Units\Mass\Weighable;
use Isotope\Haste\Units\Mass\WeightAggregate;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Shipping;

abstract class AbstractShipping extends AbstractCheckoutMethod implements ShippingInterface, WeightAggregate
{
    protected float $minWeight = 0;

    protected float $maxWeight = 0;

    protected Weighable|null $shippingWeight = null;

    public function setMinMaxWeight(float $minWeight, float $maxWeight): static
    {
        $this->minWeight = $minWeight;
        $this->maxWeight = $maxWeight;

        return $this;
    }

    public function setShippingWeight(Weighable|null $shippingWeight): static
    {
        $this->shippingWeight = $shippingWeight;

        return $this;
    }

    public function getSurcharge(IsotopeOrderableCollection $collection): Shipping|null
    {
        if (null === $this->getPrice($collection)) {
            return null;
        }

        return ProductCollectionSurcharge::createForShippingInCollection($this, $collection);
    }

    public function getWeight(): Weighable|null
    {
        return $this->shippingWeight;
    }
}
