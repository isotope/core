<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\CheckboxAttribute;
use Isotope\CoreBundle\Attribute\RadioAttribute;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\AttributeModel;

/**
 * Configures the options wizard in attribute configuration.
 */
#[AsCallback('tl_iso_attribute', 'fields.options.attributes')]
class OptionsWizardListener
{
    public function __construct(private readonly AttributeRegistry $attributeRegistry)
    {
    }

    /**
     * @param DataContainer|mixed $dc
     */
    public function __invoke(array $attributes, $dc): array
    {
        if (!$dc instanceof DataContainer) {
            return $attributes;
        }

        $attribute = $this->attributeRegistry->get(AttributeModel::findById($dc->id));

        if ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption()) {
            unset($attributes['fields']['group'], $attributes['fields']['default']);
        } elseif ($attribute instanceof CheckboxAttribute || $attribute instanceof RadioAttribute) {
            unset($attributes['fields']['group']);
        }

        return $attributes;
    }
}
