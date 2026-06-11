<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsProductCollectionAction implements TypeAttributeInterface
{
    use ClassTypeTrait;

    public const TAG_NAME = 'isotope.product_collection_action';

    public function __construct(
        public string|null $type = null,
        public int $priority = 0,
    ) {
    }

    public function getType(string $className): string
    {
        return $this->type ?? $this->removeSuffix($className, ['ProductCollectionAction', 'CollectionAction', 'Action']);
    }
}
