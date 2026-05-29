<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsCheckoutStep implements TypeAttributeInterface
{
    use ClassTypeTrait;

    public const string TAG_NAME = 'isotope.checkout_step';

    public function __construct(
        public string|null $type = null,
        public int $priority = 0,
    ) {
    }

    public function getType(string $className): string
    {
        return $this->type ?? $this->removeSuffix($className, ['CheckoutStep', 'Step']);
    }
}
