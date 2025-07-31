<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;

interface ProductRendererInterface
{
    public function render(Product $product, RenderConfig $config, Request $request): string;
}
