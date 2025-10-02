<?php

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Twig\Environment;

class UpdateAction implements ProductActionInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function supports(ProductInterface $product, ViewInterface $view): bool
    {
        return true;
    }

    public function render(ProductInterface $product, ViewInterface $view): string
    {
        return $this->twig->render('@Contao/isotope/product_action/update.html.twig');
    }

    public function handle(ProductInterface $product, array $configuration, ViewInterface $view): ActionResult
    {
        return new ActionResult();
    }
}
