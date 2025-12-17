<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Codefog\HasteBundle\Formatter;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
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

    public function getValue(string $attributeName, mixed $value): array
    {
        $attribute = $this->attributeRegistry->get($attributeName);
        $value = \is_array($value) ? $value : [$value];

        if ($attribute instanceof OptionsAttributeInterface) {
            $options = $attribute->getOptions();

            return array_map(
                static fn ($value) => $options->getLabel((string) $value) ?? $value,
                $value
            );
        }

        return $value;
    }
}
