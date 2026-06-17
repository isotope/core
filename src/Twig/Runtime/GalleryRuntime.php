<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Runtime;

use Isotope\CoreBundle\Model\GalleryModel;
use Isotope\CoreBundle\ModelType\GalleryRegistry;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Twig\Extension\RuntimeExtensionInterface;

class GalleryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly GalleryRegistry $galleryRegistry,
        private readonly StoreManager $storeManager,
    ) {
    }

    public function get(array $context, CoreProduct $product, string $attributeName, int|null $galleryId = null, ProductReference|string|null $href = null): TemplateMarkup|null
    {
        $view = $context['view'] ?? null;

        if (!$galleryId && !$view instanceof ViewInterface) {
            return null;
        }

        $galleryId ??= $view->getGallery($product);
        $model = $galleryId > 0 ? GalleryModel::findById($galleryId) : null;

        if (!$model) {
            return null;
        }

        return $this->galleryRegistry
            ->get($model, $this->storeManager->getCurrentContext())
            ->build($product, $attributeName, $href ?? $view?->getProductReference($product))
        ;
    }
}
