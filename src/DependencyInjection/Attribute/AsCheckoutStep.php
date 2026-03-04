<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsCheckoutStep
{
    public const string TAG_NAME = 'isotope.checkout_step';

    public function __construct(
        public int $priority = 0,
    ) {
    }
}
