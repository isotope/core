<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Codefog\HasteBundle\Formatter;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AttributeRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Formatter $formatter,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getLabel(string $attributeName): string
    {
        $attribute = $this->attributeRegistry->get($attributeName);

        try {
            if ($attribute) {
                // TODO: name only exists in the AbstractAttribute
                return $this->translator->trans($attribute->getName(), [], 'isotope');
            }
        } catch (\Exception) {}

        return $this->formatter->dcaLabel('tl_iso_product', $attributeName);
    }

    public function getValue(string $attribute, mixed $value): string
    {
        return $this->formatter->dcaValue('tl_iso_product', $attribute, $value);
    }
}
