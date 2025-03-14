<?php

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollectionSurcharge\Shipping;

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
    public function isAvailable(IsotopeOrderableCollection $collection): bool;

    /**
     * Return true if shipping price is not a fixed amount.
     */
    public function isPercentage(): bool;

    /**
     * Get the percentage amount (if applicable).
     */
    public function getPercentage(): float;

    /**
     * Return label for the shipping method.
     */
    public function getLabel(IsotopeOrderableCollection $collection): string;

    /**
     * Returns the shipping note.
     */
    public function getNote(IsotopeOrderableCollection $collection): string;

    /**
     * Return the calculated total price for shipping.
     */
    public function getPrice(IsotopeOrderableCollection $collection): float|null;

    /**
     * Return percentage label if price is percentage.
     */
    public function getPercentageLabel(): string;

    /**
     * Return the tax class ID for this shipping method.
     */
    public function getTaxClass(IsotopeOrderableCollection $collection): int;

    /**
     * Return the checkout review information.
     * Use this to return custom checkout information about this shipping module.
     * Example: Information about tracking codes.
     */
    public function checkoutReview(IsotopeOrderableCollection $collection): string;

    /**
     * Get the checkout surcharge for this shipping method.
     */
    public function getSurcharge(IsotopeOrderableCollection $collection): Shipping|null;
}
