<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\Model;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\GalleryModel;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @template TType of AbstractGallery
 *
 * @template-implements ModelTypeFactoryInterface<TType, GalleryModel>
 */
abstract class AbstractGalleryFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    /**
     * @param GalleryModel $model
     *
     * @return TType
     */
    public function create(Model $model, StoreContext $context): AbstractGallery
    {
        $this->validateInstanceOf($model, GalleryModel::class);

        $instance = $this->doCreate($model)
            ->setMainSize(StringUtil::deserialize($model->main_size, true) ?: null)
            ->setGallerySize(StringUtil::deserialize($model->gallery_size, true) ?: null)
            ->setLightboxSize($model->lightbox_size)
            ->setPlaceholder($model->placeholder)
        ;

        if (null !== ($anchor = AnchorTarget::tryFrom($model->anchor))) {
            $instance->setAnchor($anchor);
        }

        if ($model->customTpl) {
            $instance->setTemplate('@Contao/'.$model->customTpl.'.html.twig');
        }

        return $instance;
    }

    /**
     * @return TType
     */
    abstract protected function doCreate(Model $model): AbstractGallery;
}
