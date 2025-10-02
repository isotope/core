<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\StringUtil;
use Isotope\CoreBundle\Gallery\StandardGallery;
use Isotope\CoreBundle\Model\Gallery;
use Isotope\CoreBundle\ModelType\GalleryRegistry;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\Widget\MediaManager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

// TODO: remove public:true once the product list (and product Standard::generate) don't fall back to it
#[Autoconfigure(public: true)]
class GalleryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly GalleryRegistry $galleryRegistry,
        private readonly Studio $imageStudio,
        private readonly Environment $twig,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function get(array $context, string $attributeName, int|null $galleryId = null): GalleryVariable|null
    {
        $product = $context['product'] ?? null;
        $view = $context['view'] ?? null;

        if (!$product instanceof ProductInterface || (!$galleryId && !$view instanceof ViewInterface)) {
            return null;
        }

        $galleryId ??= $view->getGallery($product);
        $model = $galleryId > 0 ? Gallery::findById($galleryId) : null;

        $gallery = $model ? $this->galleryRegistry->get($model) : new StandardGallery(0, $this->imageStudio, $this->twig);

        $href = null;
        $files = MediaManager::mergeMediaData(
            StringUtil::deserialize($product[$attributeName], true),
            StringUtil::deserialize($product[$attributeName.'_fallback'], true),
        );

        //if (!isset($view['jumpTo']) || ProductResolver::canLinkToPage($view['jumpTo'])) {
        //    $href = $this->urlGenerator->generate(new ProductReference($product, $view['jumpTo'] ?? null));
        //}

        return new GalleryVariable($gallery->withProductData(
            'iso_product_'.$product->getId().'_'.$attributeName,
            $product->getProductId(),
            $files,
            $href,
        ));
    }
}
