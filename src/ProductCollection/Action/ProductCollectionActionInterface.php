<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\Interfaces\IsotopeProductCollection;

interface ProductCollectionActionInterface
{
    /**
     * Returns whether the action is supported for the given product collection.
     */
    public function supports(IsotopeProductCollection $collection, array $context): bool;

    /**
     * Renders HTML content for this action.
     */
    public function render(IsotopeProductCollection $collection, array $context): string;

    /**
     * Handles submit of the product collection.
     */
    public function handle(IsotopeProductCollection $collection, array $context): ActionResult;
}
