<?php

namespace Isotope\Frontend\ProductAction;

use Isotope\Interfaces\IsotopeProduct;

interface ProductActionInterface
{
    /**
     * Gets the internal name of the action.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the label for the action.
     *
     * @param IsotopeProduct|null $product
     *
     * @return string
     */
    public function getLabel(IsotopeProduct $product = null);

    /**
     * Returns whether the action is available for the given product.
     *
     * @param IsotopeProduct $product
     * @param array          $config
     *
     * @return bool
     */
    public function isAvailable(IsotopeProduct $product, array $config = []);

    /**
     * Generates HTML content for this action.
     *
     * @param IsotopeProduct $product
     * @param array          $config
     *
     * @return string
     */
    public function generate(IsotopeProduct $product, array $config = []);

    /**
     * Handles submit of the product. Must self-check if this action applies.
     *
     * @param IsotopeProduct $product
     * @param array          $config
     *
     * @return bool
     */
    public function handleSubmit(IsotopeProduct $product, array $config = []);
}
