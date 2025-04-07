<?php

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\Studio;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Twig\Environment;

#[AsGallery]
class InlineGallery extends StandardGallery
{
    public function __construct(int $id, Studio $imageStudio, Environment $twig)
    {
        parent::__construct($id, $imageStudio, $twig);

        $this->setTemplate('@Contao/isotope/gallery/inline.html.twig');
    }

    public function generateGallery(int $skip = 0): string
    {
        return parent::generateGallery($skip);
    }

    protected function prepareFigureBuilder(string $type, int $index, array $file, int|array|string|null $size): FigureBuilder
    {
        $figureBuilder = parent::prepareFigureBuilder($type, $index, $file, $size);

        if ('gallery' === $type) {
            if ($this->getAnchor() === AnchorTarget::lightbox) {
                $figureBuilder->setLinkAttribute('data-lightbox-url', $figureBuilder->buildIfResourceExists()?->getLinkHref());
            }

            $figure = $this->prepareFigureBuilder('main', $index, $file, $this->getMainSize())->buildIfResourceExists();
            $figureBuilder->setLinkHref($figure?->getImage()->getImageSrc());
        }

        return $figureBuilder;
    }
}
