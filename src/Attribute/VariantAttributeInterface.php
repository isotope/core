<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

interface VariantAttributeInterface extends AttributeInterface
{
    /**
     * Returns true if attribute is a variant option.
     */
    public function isVariantOption(): bool;
}
