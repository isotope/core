<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\Model\Product;

/**
 * Interface for attributes that supports range filter, meaning the value can be "between" a range,
 * e.g. "price more than 5 and less than 10".
 */
interface RangeAttributeInterface extends AttributeInterface
{
    /**
     * Returns whether range filter can be used on this attribute.
     */
    public function supportsRangeFilter(): bool;

    /**
     * Gets array of values for range filter.
     */
    public function getValueRange(Product $product): array|null;
}
