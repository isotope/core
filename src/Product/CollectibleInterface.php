<?php

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Model\ProductType;
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProductCollection;

interface CollectibleInterface extends ShippableInterface
{
    public function getType(): string;

    public function getId(): int|string;

    /**
     * Returns the product type for this product, or null if not applicable or not found.
     */
    public function getProductType(): ProductType|null;

    /**
     * Returns the product name. Used to store as fallback in the product collection.
     */
    public function getName(): string;

    /**
     * Returns the product SKU. Used to store as fallback in the product collection.
     */
    public function getSku(): string;

    /**
     * Returns true if the product is available in the frontend
     *
     * @return bool
     */
    public function isAvailableInFrontend();

    /**
     * Returns true if the product is available in the given collection
     *
     *
     * @return bool
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection);

    /**
     * Get product price model
     *
     *
     * @return IsotopePrice
     */
    public function getPrice(IsotopeProductCollection $objCollection = null);

    /**
     * Return minimum quantity for the product (from advanced price tiers)
     *
     * @return int
     */
    public function getMinimumQuantity();
}
