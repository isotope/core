<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Twig\TemplateContext;
use Symfony\Component\HttpFoundation\Request;

interface ProductRendererInterface
{
    public function render(ProductContext $productContext, ViewInterface $view, Request $request): TemplateContext;
}
