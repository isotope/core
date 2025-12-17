<?php

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;

interface ProductCollectionItemActionInterface
{
    /**
     * Returns whether the action is supported for the given product.
     */
    public function supports(AbstractProductCollection $collection, LineItem $item, array $context): bool;

    /**
     * Renders HTML content for this action.
     */
    public function render(AbstractProductCollection $collection, LineItem $item, array $context): string;

    /**
     * Handles submit of the product collection.
     */
    public function handle(AbstractProductCollection $collection, LineItem $item, array $context): ActionResult;
}
