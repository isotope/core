<?php

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\RenderConfig;

interface ProductActionInterface
{
    /**
     * Returns whether the action is supported for the given product.
     */
    public function supports(ProductInterface $product, RenderConfig $renderConfig): bool;

    /**
     * Generates HTML content for this action.
     */
    public function render(ProductInterface $product, RenderConfig $renderConfig): string;

    /**
     * Handles submit of the product action.
     */
    public function handle(ProductInterface $product, array $configuration, RenderConfig $renderConfig): ActionResult;
}
