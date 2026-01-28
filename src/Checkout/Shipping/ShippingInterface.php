<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\Interfaces\IsotopeOrderableCollection;

/**
 * Interface for an Isotope shipping method.
 */
interface ShippingInterface
{
    /**
     * Returns the ID of this shipping method.
     */
    public function getId(): int;

    /**
     * Return boolean flag if the shipping method is available.
     */
    public function isAvailable(StoreContext $context): bool;

    /**
     * Return true if shipping price is not a fixed amount.
     *
     * TODO: remove this
     * @deprecated
     */
    public function isPercentage(): bool;

    /**
     * Get the percentage amount (if applicable).
     *
     * TODO: remove this
     * @deprecated
     */
    public function getPercentage(): float;

    /**
     * Return label for the shipping method.
     */
    public function getLabel(): string;

    /**
     * Returns the shipping note.
     */
    public function getNote(): string;

    public function getLevel(): int;

    public function getPriceDefinition(): PriceDefinition|null;

    /**
     * Return the calculated total price for shipping.
     *
     * TODO: remove this
     * @deprecated
     */
    public function getPrice(IsotopeOrderableCollection $collection): float|null;

    /**
     * Return percentage label if price is percentage.
     *
     * TODO: remove this
     * @deprecated
     */
    public function getPercentageLabel(): string;

    /**
     * Return the tax class ID for this shipping method.
     *
     * TODO: remove this
     * @deprecated
     */
    public function getTaxClass(IsotopeOrderableCollection $collection): int;

    /**
     * Return the checkout review information.
     * Use this to return custom checkout information about this shipping module.
     * Example: Information about tracking codes.
     */
    public function checkoutReview(IsotopeOrderableCollection $collection): string;
}
