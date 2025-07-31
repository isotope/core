<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Isotope\CoreBundle\Gallery\GalleryInterface;

class GalleryVariable
{
    public function __construct(
        private readonly GalleryInterface $gallery,
    ) {
    }

    public function getGallery(): GalleryInterface
    {
        return $this->gallery;
    }

    public function main_image(): string
    {
        return $this->gallery->generateMainImage();
    }

    public function gallery_images(int $skip = 1): string
    {
        return $this->gallery->generateGallery($skip);
    }
}
