<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\Model;
use Isotope\CoreBundle\Model\GalleryModel;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\StringUtil;

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
            ->setMainSize(StringUtil::unserialize($model->main_size))
            ->setGallerySize(StringUtil::unserialize($model->gallery_size))
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
