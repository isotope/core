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
        private readonly Studio $imageStudio,
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param Gallery $model
     */
    protected function doCreate(Model $model): AbstractGallery
    {
        $gallery = new StandardGallery($model->id, $this->imageStudio, $this->twig);
        $gallery->setLightboxSize($model->lightbox_size);

        return $gallery;
    }
}
