<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

#[AsAttribute]
class TextAttribute extends AbstractAttribute
{
    use TranslatableTrait;

    private int|null $minlength = null;

    private int|null $maxlength = null;

    private string|null $rgxp = null;

    private bool $datepicker = false;

    public function getMinlength(): int|null
    {
        return $this->minlength;
    }

    public function setMinlength(int|null $minlength): self
    {
        $this->minlength = $minlength;

        return $this;
    }

    public function getMaxlength(): int|null
    {
        return $this->maxlength;
    }

    public function setMaxlength(int|null $maxlength): self
    {
        $this->maxlength = $maxlength;

        return $this;
    }

    public function getRgxp(): string|null
    {
        return $this->rgxp;
    }

    public function setRgxp(string|null $rgxp): self
    {
        $this->rgxp = $rgxp;

        return $this;
    }

    public function isDatepicker(): bool
    {
        return $this->datepicker;
    }

    public function setDatepicker(bool $datepicker): self
    {
        $this->datepicker = $datepicker;

        return $this;
    }

    #[\Override]
    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        $field['eval']['minlength'] = $this->minlength;
        $field['eval']['maxlength'] = $this->maxlength;
        $field['eval']['rgxp'] = $this->rgxp;
        $field['eval']['datepicker'] = $this->datepicker;

        if ($this->isTranslatable()) {
            $field['eval']['translatableFor'] = '*';
        }
    }

    protected function getSqlDefinition(): array|string
    {
        return [
            'type' => Types::STRING,
            'length' => $this->maxlength > 0 ? $this->maxlength : 255,
            'default' => '',
        ];
    }
}
