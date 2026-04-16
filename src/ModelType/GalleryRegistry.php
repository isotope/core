<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Gallery\GalleryInterface;
use Isotope\CoreBundle\Model\GalleryModel;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<GalleryInterface, GalleryModel>
 */
#[Autoconfigure(public: true)]
class GalleryRegistry extends AbstractModelTypeRegistry
{
    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, GalleryInterface::class);
    }
}
