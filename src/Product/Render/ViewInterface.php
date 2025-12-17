<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Routing\ProductReference;

interface ViewInterface
{
    public function getFormId(CoreProduct $product): string;

    public function getTemplate(CoreProduct $product): string;

    public function getGallery(CoreProduct $product): int;

    public function getProductReference(CoreProduct $product): ProductReference;

    public function getActions(CoreProduct $product): array;

    public function useQuantity(CoreProduct $product): bool;

    public function useOptions(CoreProduct $product): bool;

    public function useFallbackVariant(CoreProduct $product): bool;

    public function get(string $key): mixed;

    public function toArray(CoreProduct $product): array;
}
