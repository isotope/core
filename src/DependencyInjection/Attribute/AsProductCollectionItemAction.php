<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsProductCollectionItemAction
{
    public function __construct(
        public string|null $name = null,
        public int $priority = 0,
    ) {
    }
}
