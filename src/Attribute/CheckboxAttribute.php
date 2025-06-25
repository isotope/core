<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Widget;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

#[AsAttribute]
class CheckboxAttribute extends AbstractOptionsAttribute
{
    public function hasMultiple(): bool
    {
        return OptionsSource::Name !== $this->optionsSource;
    }

    public function prepareOptionsWizard(Widget $widget, array $columns): array
    {
        unset($columns['group']);

        return $columns;
    }

    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        $field['eval']['multiple'] = $this->hasMultiple();

        if (OptionsSource::Name === $this->optionsSource) {
            unset($field['options']);
        }
    }

    protected function getSqlDefinition(): array|string
    {
        if (OptionsSource::Name === $this->optionsSource) {
            return ['type' => Types::BOOLEAN, 'options' => ['default' => false]];
        }

        return ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB];
    }
}
