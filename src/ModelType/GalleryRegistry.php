<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Gallery\GalleryInterface;
use Isotope\CoreBundle\Model\Gallery;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<GalleryInterface, Gallery>
 */
#[Autoconfigure(public: true)]
class GalleryRegistry extends AbstractModelTypeRegistry
{
    public const TAG_NAME = 'isotope.gallery';

    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, GalleryInterface::class);
    }
}
