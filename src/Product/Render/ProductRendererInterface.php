<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Isotope\CoreBundle\Product\ProductInterface;
use Symfony\Component\HttpFoundation\Request;

interface ProductRendererInterface
{
    public function render(ProductInterface $product, RenderConfig $config, Request $request): string;
}
