<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\System;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethod;
use Isotope\Haste\Units\Mass\Weighable;
use Isotope\Haste\Units\Mass\WeightAggregate;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Shipping;

abstract class AbstractShipping extends AbstractCheckoutMethod implements ShippingInterface, WeightAggregate
{
    /**
     * @var array<string>|null
     */
    protected array|null $allowedCountries = null;

    /**
     * @var array<string>|null
     */
    protected array|null $allowedSubdivisions = null;

    /**
     * @var array<string>|null
     */
    protected array|null $allowedPostalCodes = null;

    protected float $minWeight = 0;

    protected float $maxWeight = 0;

    protected Weighable|null $shippingWeight = null;

    protected AddressType|null $addressType = null;

    public function setAllowedAddress(array|null $countryCodes, array|null $subdivisions, array|null $postalCodes): static
    {
        $this->allowedCountries = $countryCodes;
        $this->allowedSubdivisions = $subdivisions;
        $this->allowedPostalCodes = $postalCodes;

        return $this;
    }

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

    public function setAddressType(AddressType|null $addressType): static
    {
        $this->addressType = $addressType;

        return $this;
    }

    public function isAvailable(IsotopeOrderableCollection $collection): bool
    {
        if (!parent::isAvailable($collection)) {
            return false;
        }

        $shippingAddress = $collection->getShippingAddress();

        if (null !== $this->allowedCountries) {
            if (!\in_array($shippingAddress?->country, $this->allowedCountries, true)) {
                return false;
            }

            if (
                null !== $this->allowedSubdivisions
                && !\in_array($shippingAddress->subdivision, $this->allowedSubdivisions, true)
            ) {
                return false;
            }
        }

        if (null !== $this->allowedPostalCodes && !\in_array((string) $shippingAddress?->postal, $this->allowedPostalCodes, true)) {
            return false;
        }

        // !HOOK: modify if shipping method is available
        if (isset($GLOBALS['ISO_HOOKS']['shippingAvailable']) && \is_array($GLOBALS['ISO_HOOKS']['shippingAvailable'])) {
            foreach ($GLOBALS['ISO_HOOKS']['shippingAvailable'] as $callback) {
                if (!System::importStatic($callback[0])->{$callback[1]}($this)) {
                    return false;
                }
            }
        }

        return true;
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
