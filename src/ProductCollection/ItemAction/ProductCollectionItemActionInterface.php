<?php

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollectionItem;

interface ProductCollectionItemActionInterface
{
    /**
     * Returns whether the action is supported for the given product.
     */
    public function supports(ProductCollectionItem $item, array $context): bool;

    /**
     * Renders HTML content for this action.
     */
    public function render(ProductCollectionItem $item, array $context): string;

    /**
     * Handles submit of the product collection.
     */
    public function handle(ProductCollectionItem $item, array $context): ActionResult;
}
