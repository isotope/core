<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\Model\AttributeOption;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\DcawizardBundle\Widget\DcaWizard;

/**
 * Show price column in dcaWizard if attribute is not a variant option.
 */
#[AsCallback('tl_iso_attribute', 'fields.optionsTable.foreignTable', singleton: true)]
class TableOptionsPriceListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AttributeRegistry $attributeRegistry,
    ) {
    }

    public function __invoke(DcaWizard $widget): string
    {
        if ('iso_products' === $this->requestStack->getCurrentRequest()?->query->get('do')) {
            $attribute = $this->attributeRegistry->get($widget->name);
        } else {
            $attribute = $this->attributeRegistry->get($widget->activeRecord->field_name);
        }

        if ($attribute instanceof VariantAttributeInterface && !$attribute->isVariantOption()) {
            $widget->fields = array_merge($widget->fields, ['price']);
        }

        return AttributeOption::getTable();
    }
}
