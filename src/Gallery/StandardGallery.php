<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\Studio;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;

#[AsGallery]
class StandardGallery extends AbstractGallery implements ContainerAwareInterface
{
    public static function getSubscribedServices(): array
    {
        return [
            TemplateMarkupFactory::class,
            Studio::class,
        ];
    }

    public function build(CoreProduct $product, string $attribute, ProductReference|null $productReference = null): TemplateMarkup|null
    {
        return $this->get(TemplateMarkupFactory::class)->create($this->getTemplate(), [
            'files' => $this->getFiles($product, $attribute),
            'gallery' => $this,
            'product' => $product,
            'product_reference' => $productReference,
            'figure_builder' => $this->get(Studio::class)->createFigureBuilder(),
        ]);
    }
}
