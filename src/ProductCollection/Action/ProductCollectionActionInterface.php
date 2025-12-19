<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;

interface ProductCollectionActionInterface
{
    /**
     * Returns whether the action is supported for the given product collection.
     */
    public function supports(AbstractProductCollection $collection, array $context): bool;

    /**
     * Renders HTML content for this action.
     */
    public function render(AbstractProductCollection $collection, array $context): string;

    /**
     * Handles submit of the product collection.
     */
    public function handle(AbstractProductCollection $collection, array $context): ActionResult;
}
