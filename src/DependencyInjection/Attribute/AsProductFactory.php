<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsProductFactory implements TypeAttributeInterface
{
    use ClassTypeTrait;

    public const string TAG_NAME = 'isotope.product_factory';

    public function __construct(
        public string|null $type = null,
        public int $priority = 0,
    ) {
    }

    public function getType(string $className): string
    {
        return $this->type ?? $this->removeSuffix($className, ['ProductFactory', 'Factory']);
    }
}
