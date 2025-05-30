<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendTemplateListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsCallback('tl_iso_attribute', 'config.onpalette')]
    public function onPalette(string $palette, DataContainer $dc): string
    {
        $attribute = $this->attributeRegistry->get($dc->getCurrentRecord()['field_name']);

        if ($attribute?->isCustomerDefined() || ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption())) {
            $palette = PaletteManipulator::create()
                ->addLegend('template_legend', '', PaletteManipulator::POSITION_APPEND)
                ->addField('customTpl', 'template_legend', PaletteManipulator::POSITION_APPEND)
                ->applyToString($palette)
            ;
        }

        return $palette;
    }

    #[AsCallback('tl_iso_attribute', 'fields.customTpl.options')]
    public function getOptions(DataContainer $dc): array
    {
        if ('overrideAll' === $this->requestStack->getCurrentRequest()?->query->get('act')) {
            return Controller::getTemplateGroup('form_');
        }

        $prefix = 'form_'.$dc->getCurrentRecord()['type'];

        return Controller::getTemplateGroup($prefix.'_', [], $prefix);
    }
}
