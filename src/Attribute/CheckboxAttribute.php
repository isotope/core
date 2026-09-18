<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

#[AsAttribute]
class CheckboxAttribute extends AbstractOptionsAttribute
{
    #[\Override]
    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        if (OptionsSource::Name === $this->getOptionsSource()) {
            $field['eval']['multiple'] = false;
            unset($field['options'], $field['options_callback'], $field['foreignKey']);
        } else {
            $field['eval']['multiple'] = true;

            if (
                OptionsSource::Table === $this->getOptionsSource()
                || OptionsSource::ForeignKey === $this->getOptionsSource()
            ) {
                $field['eval']['csv'] = ',';
            }
        }
    }

    protected function getSqlDefinition(): array|string
    {
        if (OptionsSource::Name === $this->getOptionsSource()) {
            return ['type' => Types::BOOLEAN, 'options' => ['default' => false]];
        }

        return ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false];
    }
}
