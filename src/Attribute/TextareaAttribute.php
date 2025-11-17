<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

#[AsAttribute]
class TextareaAttribute extends AbstractAttribute
{
    use TranslatableTrait;

    private string|null $rte = null;

    public function getRte(): string|null
    {
        return $this->rte;
    }

    public function setRte(string|null $rte): self
    {
        $this->rte = $rte;

        return $this;
    }

    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        if ($this->rte) {
            $field['eval']['rte'] = $this->rte;
            $field['eval']['tl_class'] = 'clr';
        }

        if ($this->isTranslatable()) {
            $field['eval']['translatableFor'] = '*';
        }
    }

    protected function getSqlDefinition(): array|string
    {
        return ['type' => Types::TEXT];
    }
}
