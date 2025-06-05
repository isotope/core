<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Widget;
use Isotope\Collection\AttributeOption;
use Isotope\Interfaces\IsotopeProduct;

interface OptionsAttributeInterface extends AttributeInterface
{
    /**
     * Returns the options source.
     */
    public function getOptionsSource(): OptionsSource|null;

    /**
     * Adjust the attribute option wizard for this widget in the back end.
     */
    public function prepareOptionsWizard(Widget $widget, array $columns): array;

    /**
     * Get field options.
     */
    public function getOptionsForWidget(IsotopeProduct|null $product = null): array;

    /**
     * Get AttributeOption models for current attribute.
     */
    public function getOptionsFromManager(IsotopeProduct|null $product = null): AttributeOption|null;

    /**
     * Get a list of options for the frontend product filter.
     */
    public function getOptionsForProductFilter(array $values): array;

    /**
     * Return true if attribute can have prices.
     */
    public function canHavePrices(): bool;
}
