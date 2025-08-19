<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Codefog\HasteBundle\Formatter;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Twig\Extension\RuntimeExtensionInterface;

class AttributeRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Formatter $formatter,
        private readonly AttributeRegistry $attributeRegistry,
    ) {
    }

    public function getLabel(string $attribute): string
    {
        try {
            // TODO: name only exists in the AbstractAdvertisableProduct
            return $this->attributeRegistry->get($attribute)->getName();
        } catch (\Exception) {
            return $this->formatter->dcaLabel('tl_iso_product', $attribute);
        }
    }

    public function getValue(string $attribute, mixed $value): string
    {
        return $this->formatter->dcaValue('tl_iso_product', $attribute, $value);
    }
}
