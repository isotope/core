<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Routing\ProductReference;

interface ViewInterface
{
    public function getFormId(ProductInterface $product): string;

    public function getTemplate(ProductInterface $product): string;

    public function getGallery(ProductInterface $product): int;

    public function getProductReference(ProductInterface $product): ProductReference;

    public function getActions(ProductInterface $product): array;

    public function useQuantity(ProductInterface $product): bool;

    public function useOptions(ProductInterface $product): bool;

    public function useFallbackVariant(ProductInterface $product): bool;

    public function get(string $key): mixed;

    public function toArray(ProductInterface $product): array;
}
