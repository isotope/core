<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\Studio;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Twig\TemplateContext;
use Twig\Environment;

#[AsGallery]
class StandardGallery extends AbstractGallery
{
    public function __construct(
        int $id,
        string $template,
        protected readonly Environment $twig,
        protected readonly Studio $imageStudio,
    ) {
        parent::__construct($id, $template);
    }

    public function build(CoreProduct $product, string $attribute, ProductReference|null $productReference = null): TemplateContext|null
    {
        return new TemplateContext($this->twig, $this->getTemplate(), [
            'files' => $this->getFiles($product, $attribute),
            'gallery' => $this,
            'product' => $product,
            'product_reference' => $productReference,
            'figure_builder' => $this->imageStudio->createFigureBuilder(),
        ]);
    }
}
