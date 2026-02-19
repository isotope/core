<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsProductFilter
{
    public const string TAG_NAME = 'isotope.product_filter';

    public function __construct(
        public string $operator,
        public int $priority = 0,
    ) {
    }
}
