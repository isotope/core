<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Widget;
use Isotope\CoreBundle\Product\CoreProduct;

/**
 * A product attribute for Isotope eCommerce.
 */
interface AttributeInterface
{
    /**
     * Returns the DCA field name.
     */
    public function getFieldName(): string;

    /**
     * Returns true if attribute is customer defined.
     * This means the attribute is editable in the front end and not the back end.
     */
    public function isCustomerDefined(): bool;

    /**
     * Return inputType for the backend widget or empty if none should be available.
     */
    public function getInputType(): string|null;

    /**
     * Return class name for the frontend widget or empty if none should be available.
     *
     * @return class-string<Widget>|null
     */
    public function getFrontendWidget(): string|null;

    /**
     * Save attribute configuration into the given product DCA field array.
     *
     * @param array $field A reference to the field in $GLOBALS['TL_DCA']['tl_iso_product']['fields']
     * @param array $keys  A reference to $GLOBALS['TL_DCA']['tl_iso_product']['config']['sql']['keys']
     */
    public function updateDcaField(array &$field, array &$keys): void;

    /**
     * Gets attribute value from given product.
     */
    public function getValue(CoreProduct $product): mixed;
}
