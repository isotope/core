<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Model;
use Isotope\CoreBundle\Model\Gallery;
use Twig\Environment;

/**
 * @extends AbstractGalleryFactory<StandardGallery>
 */
class StandardGalleryFactory extends AbstractGalleryFactory
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Studio $imageStudio,
    ) {
    }

    /**
     * @param Gallery $model
     */
    protected function doCreate(Model $model): AbstractGallery
    {
        return new StandardGallery(
            (int) $model->id,
            '@Contao/isotope/gallery/standard.html.twig',
            $this->twig,
            $this->imageStudio,
        );
    }
}
