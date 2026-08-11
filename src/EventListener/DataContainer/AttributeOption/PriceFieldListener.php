<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\AttributeOption;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\AttributeModel;
use Isotope\CoreBundle\Model\AttributeOptionModel;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Price is not available for an attribute option if the attribute is a variant option attribute.
 */
#[AsCallback('tl_iso_attribute_option', 'config.onload')]
class PriceFieldListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AttributeRegistry $attributeRegistry,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ('iso_products' === $this->requestStack->getCurrentRequest()?->query->get('do')) {
            return;
        }

        $attribute = $this->attributeRegistry->get(AttributeModel::findById($dc->currentPid));

        if (
            null === $attribute
            || ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption())
        ) {
            unset($GLOBALS['TL_DCA'][AttributeOptionModel::getTable()]['fields']['price']);

            PaletteManipulator::create()
                ->removeField('price')
                ->applyToPalette('option', AttributeOptionModel::getTable())
            ;
        }
    }
}
