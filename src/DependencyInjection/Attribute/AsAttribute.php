<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

use Symfony\Component\DependencyInjection\Container;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsAttribute implements TypeAttributeInterface
{
    public const TAG_NAME = 'isotope.attribute';

    public function __construct(
        public string|null $type = null,
        public int $priority = 0,
    ) {
    }

    public function getType(string $className): string
    {
        if ($this->type) {
            return $this->type;
        }

        $className = ltrim(strrchr($className, '\\'), '\\');

        if (str_ends_with($className, 'Attribute')) {
            $className = substr($className, 0, -9);
        }

        return lcfirst(Container::camelize($className));
    }
}
