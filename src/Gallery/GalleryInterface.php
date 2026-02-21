<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Twig\TemplateContext;

interface GalleryInterface
{
    public function getId(): int;

    public function build(CoreProduct $product, string $attribute, ProductReference|null $productReference = null): TemplateContext|null;
}
