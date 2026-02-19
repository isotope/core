<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsGallery
{
    public const TAG_NAME = 'isotope.gallery';

    public function __construct(
        public string|null $type = null,
        public string|null $factory = null,
        public int $priority = 0,
    ) {
    }
}
