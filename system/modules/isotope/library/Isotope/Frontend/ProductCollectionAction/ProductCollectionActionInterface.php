<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;

interface ProductCollectionActionInterface
{
    /**
     * Gets the internal name of the action.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the action label.
     *
     * @param IsotopeProductCollection $collection
     *
     * @return string
     */
    public function getLabel(IsotopeProductCollection $collection);

    /**
     * Returns whether the action is available for the given product.
     *
     * @param IsotopeProductCollection $collection
     *
     * @return bool
     */
    public function isAvailable(IsotopeProductCollection $collection);

    /**
     * Generates HTML content for this action.
     *
     * @param IsotopeProductCollection $collection
     *
     * @return string
     */
    public function generate(IsotopeProductCollection $collection);

    /**
     * Handles submit of the product collection. Must self-check if this action applies.
     *
     * @param IsotopeProductCollection $collection
     *
     * @return bool
     */
    public function handleSubmit(IsotopeProductCollection $collection);
}
