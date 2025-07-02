<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Producttype;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_producttype', 'config.onload')]
class DisableVariantAttributesListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ([] !== $this->attributeRegistry->getVariantOptionFields()) {
            return;
        }

        unset($GLOBALS['TL_DCA']['tl_iso_producttype']['subpalettes']['variants']);
        $GLOBALS['TL_DCA']['tl_iso_producttype']['fields']['variants']['input_field_callback'] = fn () => \sprintf(
            '<br><p class="tl_info">%s</p>',
            $this->translator->trans('XPL.noVariantAttributes', [], 'contao_explain'),
        );
    }
}
