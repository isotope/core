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
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Routing\ProductResolver;
use Isotope\CoreBundle\Widget\MediaManager;
use Isotope\Model\Product;
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

    public function get(array $context, string $attributeName): GalleryVariable|null
    {
        $product = $context['product'] ?? null;
        $config = $context['config'] ?? null;

        if (!$product instanceof ProductInterface || !$config instanceof RenderConfig) {
            return null;
        }

        $galleryModel = $config->gallery > 0 ? Gallery::findById($config->gallery) : null;
        $gallery = $galleryModel ? $this->galleryRegistry->get($galleryModel) : new StandardGallery(0, $this->imageStudio, $this->twig);

        $href = null;
        $files = MediaManager::mergeMediaData(
            StringUtil::deserialize($product[$attributeName], true),
            StringUtil::deserialize($product[$attributeName.'_fallback'], true),
        );

        //if (!isset($config['jumpTo']) || ProductResolver::canLinkToPage($config['jumpTo'])) {
        //    $href = $this->urlGenerator->generate(new ProductReference($product, $config['jumpTo'] ?? null));
        //}

        return new GalleryVariable($gallery->withProductData(
            'iso_product_'.$product->getId().'_'.$attributeName,
            $product->getProductId(),
            $files,
            $href,
        ));
    }
}
