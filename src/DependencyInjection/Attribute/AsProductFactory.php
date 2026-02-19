<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsProductFactory
{
    public const string TAG_NAME = 'isotope.product_factory';

    public function __construct(
        public string|null $type = null,
        public int $priority = 0,
    ) {
    }
}
