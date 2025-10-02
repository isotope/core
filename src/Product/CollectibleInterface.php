<?php

namespace Isotope\CoreBundle\Product;

use Contao\FrontendUser;
use Contao\MemberModel;
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
     * Returns true if the product is available.
     * If the collection is null, the check must be done for the current cart.
     *
     * @return bool
     */
    public function isAvailable(IsotopeProductCollection|null $collection = null): bool;

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
