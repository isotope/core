<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;

interface ProductActionInterface
{
    /**
     * Returns whether the action is supported for the given product.
     */
    public function supports(CoreProduct $product, array $configuration, ViewInterface $view): bool;

    /**
     * Generates HTML content for this action.
     */
    public function render(CoreProduct $product, array $configuration, ViewInterface $view): string;

    /**
     * Handles submit of the product action.
     */
    public function handle(CoreProduct $product, array $configuration, ViewInterface $view): ActionResult;
}
