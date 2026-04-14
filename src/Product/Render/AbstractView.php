<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Util\StringUtil;

abstract class AbstractView implements ViewInterface
{
    public function __construct(
        protected readonly string $prefix,
        protected readonly string|null $template,
        protected int|null $gallery,
        protected array $config,
    ) {
    }

    public function getFormId(CoreProduct $product): string
    {
        return $this->prefix.'_product'.$product->getProductId();
    }

    public function getProductReference(CoreProduct $product): ProductReference
    {
        return new ProductReference($product);
    }

    public function getActions(CoreProduct $product): array
    {
        return StringUtil::csv($this->get('iso_buttons'), forceArray: true);
    }

    public function useQuantity(CoreProduct $product): bool
    {
        return (bool) $this->get('iso_use_quantity');
    }

    public function useOptions(CoreProduct $product): bool
    {
        return !$this->get('iso_disable_options');
    }

    public function useFallbackVariant(CoreProduct $product): bool
    {
        return true;
    }

    public function get(string $key): mixed
    {
        return $this->config[$key] ?? null;
    }

    public function toArray(CoreProduct $product): array
    {
        return [
            'id' => $this->getFormId($product),
            'template' => $this->getTemplate($product),
            'gallery' => $this->getGallery($product),
            'jumpTo' => $this->getProductReference($product)->referencePage,
            'buttons' => $this->getActions($product),
            'useQuantity' => $this->useQuantity($product),
            'disableOptions' => !$this->useOptions($product),
            'loadFallback' => $this->useFallbackVariant($product),
        ];
    }
}
