<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\Model;
use Isotope\CoreBundle\Model\GalleryModel;

/**
 * @extends AbstractGalleryFactory<StandardGallery>
 */
class StandardGalleryFactory extends AbstractGalleryFactory
{
    /**
     * @param GalleryModel $model
     */
    protected function doCreate(Model $model): AbstractGallery
    {
        return new StandardGallery(
            (int) $model->id,
            '@Contao/'.($model->customTpl ?: 'isotope/gallery/standard').'.html.twig',
        );
    }
}
