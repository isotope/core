<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Contao\CoreBundle\Image\Studio\Studio;
use Isotope\CoreBundle\Gallery\StandardGallery;
use Isotope\CoreBundle\Model\Gallery;
use Isotope\CoreBundle\ModelType\GalleryRegistry;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class GalleryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly GalleryRegistry $galleryRegistry,
        private readonly Studio $imageStudio,
        private readonly Environment $twig,
    ) {
    }

    public function get(array $context, CoreProduct $product, string $attributeName, int|null $galleryId = null): TemplateContext|null
    {
        $view = $context['view'] ?? null;

        if (!$product instanceof CoreProduct || (!$galleryId && !$view instanceof ViewInterface)) {
            return null;
        }

        $galleryId ??= $view->getGallery($product);
        $model = $galleryId > 0 ? Gallery::findById($galleryId) : null;

        $gallery = $model ? $this->galleryRegistry->get($model) : new StandardGallery(0, $this->imageStudio, $this->twig);

        return $gallery->build($product, $attributeName, $view?->getProductReference($product));
    }
}
