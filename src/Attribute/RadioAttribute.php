<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Widget;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;
use Isotope\Interfaces\IsotopeProduct;

#[AsAttribute]
class RadioAttribute extends AbstractOptionsAttribute implements VariantAttributeInterface
{
    public function prepareOptionsWizard(Widget $widget, array $columns): array
    {
        $columns = parent::prepareOptionsWizard($widget, $columns);

        unset($columns['group']);

        return $columns;
    }

    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        if (!isset($keys[$this->fieldName]) && $this->isFrontendFilterable()) {
            $keys[$this->fieldName] = 'index';
        }
    }

    public function generate(IsotopeProduct $product, array $options = []): string
    {
        return '';
    }

    protected function getSqlDefinition(): array|string
    {
        if (OptionsSource::Attribute === $this->optionsSource) {
            return ['type' => Types::STRING, 'length' => 64, 'options' => ['default' => '']];
        }

        return ['type' => Types::INTEGER, 'options' => ['unsigned' => true, 'default' => 0]];
    }
}
