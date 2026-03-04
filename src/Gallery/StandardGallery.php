<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\Studio;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;

#[AsGallery]
class StandardGallery extends AbstractGallery
{
    public function __construct(
        int $id,
        string $template,
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly Studio $imageStudio,
    ) {
        parent::__construct($id, $template);
    }

    public function build(CoreProduct $product, string $attribute, ProductReference|null $productReference = null): TemplateMarkup|null
    {
        return $this->markupFactory->create($this->getTemplate(), [
            'files' => $this->getFiles($product, $attribute),
            'gallery' => $this,
            'product' => $product,
            'product_reference' => $productReference,
            'figure_builder' => $this->imageStudio->createFigureBuilder(),
        ]);
    }
}
