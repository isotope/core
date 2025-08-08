<?php

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\Interfaces\IsotopeProduct;
use Twig\Environment;

class UpdateAction implements ProductActionInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function supports(IsotopeProduct $product, RenderConfig $renderConfig): bool
    {
        return true;
    }

    public function render(IsotopeProduct $product, RenderConfig $renderConfig): string
    {
        return $this->twig->render('@Contao/isotope/product_action/update.html.twig');
    }

    public function handle(IsotopeProduct $product, RenderConfig $renderConfig): ActionResult
    {
        return new ActionResult();
    }
}
