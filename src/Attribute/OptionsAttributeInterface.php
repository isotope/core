<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Widget;
use Isotope\Collection\AttributeOption;
use Isotope\CoreBundle\Product\ProductInterface;

interface OptionsAttributeInterface extends AttributeInterface
{
    /**
     * Returns the options source.
     */
    public function getOptionsSource(): OptionsSource|null;

    /**
     * Get field options.
     */
    public function getOptionsForWidget(ProductInterface|null $product = null): array;

    /**
     * Get AttributeOption models for current attribute.
     */
    public function getOptionsFromManager(ProductInterface|null $product = null): AttributeOption|null;

    /**
     * Get a list of options for the frontend product filter.
     */
    public function getOptionsForProductFilter(array $values): array;

    /**
     * Return true if attribute has multiple options (e.g. a select or multiple checkboxes).
     */
    public function hasMultiple(): bool;

    /**
     * Return true if attribute can have prices.
     */
    public function canHavePrices(): bool;
}
