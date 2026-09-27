<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

#[AsAttribute]
class RadioAttribute extends AbstractOptionsAttribute implements VariantAttributeInterface
{
    #[\Override]
    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        if (!isset($keys[$this->fieldName]) && $this->isFrontendFilterable()) {
            $keys[$this->fieldName] = 'index';
        }
    }

    public function hasMultiple(): bool
    {
        return false;
    }

    protected function getSqlDefinition(): array|string
    {
        if (OptionsSource::Attribute === $this->getOptionsSource()) {
            return ['type' => Types::STRING, 'length' => 64, 'default' => ''];
        }

        return ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0];
    }
}
