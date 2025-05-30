<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\PageModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Gallery\GalleryInterface;
use Isotope\CoreBundle\Gallery\StandardGallery;
use Isotope\CoreBundle\Model\Gallery;
use Isotope\CoreBundle\Widget\MediaManager;
use Isotope\Interfaces\IsotopeProduct;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Twig\Environment;

/**
 * @extends AbstractModelTypeRegistry<GalleryInterface, Gallery>
 */
#[Autoconfigure(public: true)]
class GalleryRegistry extends AbstractModelTypeRegistry
{
    public const TAG_NAME = 'isotope.gallery';

    public function __construct(
        ServiceProviderInterface $factories,
        private readonly ContaoFramework $framework,
        private readonly Studio $imageStudio,
        private readonly Environment $twig,
    ) {
        parent::__construct($factories, GalleryInterface::class);
    }

    /**
     * Create a gallery for product, falls back to standard gallery if none is defined.
     *
     * @param array{gallery: int} $config
     */
    public function getForProductAttribute(IsotopeProduct $product, string $attribute, array $config = []): GalleryInterface
    {
        $galleryModel = ($config['gallery'] ?? null) > 0 ? $this->framework->getAdapter(Gallery::class)->findById((int) $config['gallery']) : null;
        $gallery = $galleryModel ? $this->get($galleryModel) : new StandardGallery(0, $this->imageStudio, $this->twig);

        $href = null;
        $files = MediaManager::mergeMediaData(
            StringUtil::deserialize($product->$attribute, true),
            StringUtil::deserialize($product->{$attribute.'_fallback'}, true),
        );

        if (!$config['jumpTo'] instanceof PageModel || 'none' !== $config['jumpTo']->iso_readerMode) {
            $href = $product->generateUrl($config['jumpTo']);
        }

        return $gallery->withProductData(
            $product->getFormId().'_'.$attribute,
            $product->getProductId(),
            $files,
            $href,
        );
    }
}
