<?php

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\Studio;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Twig\Environment;

#[AsGallery]
class StandardGallery extends AbstractGallery
{
    private int|array|string|null $lightboxSize = null;

    public function __construct(
        int $id,
        Studio $imageStudio,
        Environment $twig,
    ) {
        parent::__construct($id, '@Contao/isotope/gallery/standard.html.twig', $imageStudio, $twig);
    }

    public function getLightboxSize(): array|int|string|null
    {
        return $this->lightboxSize;
    }

    public function setLightboxSize(array|int|string|null $lightboxSize): static
    {
        $this->lightboxSize = $lightboxSize;

        return $this;
    }

    protected function prepareFigureBuilder(string $type, int $index, array $file, int|array|string|null $size): FigureBuilder
    {
        $figureBuilder = parent::prepareFigureBuilder($type, $index, $file, $size);

        if ($this->getAnchor() !== AnchorTarget::lightbox) {
            return $figureBuilder;
        }

        // Reset existing properties
        $figureBuilder
            ->setLinkHref(null)
            ->enableLightbox(false)
        ;

        [$link, $rel] = explode('|', $file['link'] ?? '', 2) + [null, null];

        if (!$link) {
            $figureBuilder
                ->enableLightbox()
                ->setLightboxSize($this->getLightboxSize())
                ->setLightboxGroupIdentifier('product'.$this->getProductId())
            ;

            return $figureBuilder;
        }

        $figureBuilder->setLinkHref($link);

        if ($rel) {
            $figureBuilder->setLinkAttribute('data-lightbox', $rel);
        } else {
            $figureBuilder->setLinkAttribute('target', '_blank');
        }

        return $figureBuilder;
    }
}
