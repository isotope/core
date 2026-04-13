<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;

#[AsGallery]
class StandardGallery extends AbstractGallery implements ContainerAwareInterface
{
    public function __construct(int $id, string|null $template = null)
    {
        parent::__construct($id, $template ?? '@Contao/isotope/gallery/standard.html.twig');
    }

    public static function getSubscribedServices(): array
    {
        return [
            TemplateMarkupFactory::class,
            Studio::class,
            ContentUrlGenerator::class,
        ];
    }

    public function build(CoreProduct $product, string $attribute, ProductReference|string|null $href = null): TemplateMarkup|null
    {
        if ($href instanceof ProductReference) {
            $href = $this->get(ContentUrlGenerator::class)->generate($href);
        }

        return $this->get(TemplateMarkupFactory::class)->create($this->getTemplate(), [
            'files' => $this->getFiles($product, $attribute),
            'gallery' => $this,
            'product' => $product,
            'href' => $href,
            'figure_builder' => $this->get(Studio::class)->createFigureBuilder(),
        ]);
    }
}
