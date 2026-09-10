<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

/**
 * An attribute to register an Isotope hook.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsIsotopeHook
{
    public const string TAG_NAME = 'isotope.hook';

    public function __construct(
        public string $hook,
        public string|null $method = null,
        public int|null $priority = null,
    ) {
    }
}
