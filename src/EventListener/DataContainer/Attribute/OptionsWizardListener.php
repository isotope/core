<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Widget;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Model\Attribute;

/**
 * Configures the options wizard in attribute configuration.
 */
#[AsCallback('tl_iso_attribute', 'config.onload')]
class OptionsWizardListener
{
    public function __construct(private readonly AttributeRegistry $attributeRegistry)
    {
    }

    public function __invoke(): void
    {
        $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['options']['eval']['columnsCallback'] = [
            self::class,
            'getColumns',
        ];
    }

    public function getColumns(Widget $widget): array
    {
        $columns = [
            'value' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['value'],
                'inputType' => 'text',
                'eval' => ['maxlength' => 64, 'class' => 'tl_text_2'],
            ],
            'label' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['label'],
                'inputType' => 'text',
                'eval' => ['class' => 'tl_text_2'],
            ],
            'default' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['default'],
                'inputType' => 'checkbox',
                'eval' => ['columnPos' => 2],
            ],
            'group' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['group'],
                'inputType' => 'checkbox',
                'eval' => ['columnPos' => 3],
            ],
        ];

        if (
            null !== ($attribute = $this->attributeRegistry->get(Attribute::findById($widget->dataContainer->id)))
            && $attribute instanceof OptionsAttributeInterface
        ) {
            $columns = $attribute->prepareOptionsWizard($widget, $columns);
        }

        return $columns;
    }
}
