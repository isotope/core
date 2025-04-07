<?php

namespace Isotope\CoreBundle\Gallery;

interface GalleryInterface
{
    public function getId(): int;

    /**
     * Generate main image and return it as HTML string.
     */
    public function generateMainImage(): string;

    /**
     * Generate gallery and return it as HTML string.
     */
    public function generateGallery(int $skip = 1): string;

    /**
     * Creates a clone of the gallery for the given product data.
     */
    public function withProductData(string $name, int $productId, array $files, string|null $href): static;
}
