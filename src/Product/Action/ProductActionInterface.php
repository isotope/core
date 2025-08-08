<?php

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\Interfaces\IsotopeProduct;

interface ProductActionInterface
{
    /**
     * Returns whether the action is supported for the given product.
     */
    public function supports(IsotopeProduct $product, RenderConfig $renderConfig): bool;

    /**
     * Generates HTML content for this action.
     */
    public function render(IsotopeProduct $product, RenderConfig $renderConfig): string;

    /**
     * Handles submit of the product action.
     */
    public function handle(IsotopeProduct $product, RenderConfig $renderConfig): ActionResult;
}
