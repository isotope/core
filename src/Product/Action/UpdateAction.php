<?php

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Twig\Environment;

class UpdateAction implements ProductActionInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function supports(ProductInterface $product, RenderConfig $renderConfig): bool
    {
        return true;
    }

    public function render(ProductInterface $product, RenderConfig $renderConfig): string
    {
        return $this->twig->render('@Contao/isotope/product_action/update.html.twig');
    }

    public function handle(ProductInterface $product, array $configuration, RenderConfig $renderConfig): ActionResult
    {
        return new ActionResult();
    }
}
