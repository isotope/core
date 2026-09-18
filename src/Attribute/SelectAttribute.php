<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsAttribute]
class SelectAttribute extends AbstractOptionsAttribute implements VariantAttributeInterface
{
    private bool $includeBlankOption = false;

    private string|null $blankOptionLabel = null;

    private bool $multiple = false;

    private int $size = 1;

    private bool $chosen = false;

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), TranslatorInterface::class];
    }

    public function isIncludeBlankOption(): bool
    {
        return $this->includeBlankOption;
    }

    public function setIncludeBlankOption(bool $includeBlankOption): self
    {
        $this->includeBlankOption = $includeBlankOption;

        return $this;
    }

    public function getBlankOptionLabel(): string|null
    {
        return $this->blankOptionLabel;
    }

    public function setBlankOptionLabel(string|null $blankOptionLabel): self
    {
        $this->blankOptionLabel = $blankOptionLabel;

        return $this;
    }

    public function hasMultiple(): bool
    {
        return $this->multiple && !$this->isVariantOption();
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getSize(): int
    {
        return $this->isVariantOption() ? 1 : $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function hasChosen(): bool
    {
        return $this->chosen;
    }

    public function setChosen(bool $chosen): self
    {
        $this->chosen = $chosen;

        return $this;
    }

    #[\Override]
    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        $field['eval']['multiple'] = $this->hasMultiple();
        $field['eval']['size'] = $this->getSize();
        $field['eval']['chosen'] = $this->hasChosen();
        $field['eval']['includeBlankOption'] = $this->isIncludeBlankOption();

        if (
            $this->hasMultiple()
            && (
                OptionsSource::Table === $this->getOptionsSource()
                || OptionsSource::ForeignKey === $this->getOptionsSource()
            )
        ) {
            $field['eval']['csv'] = ',';
        }

        if ($blankOptionLabel = $this->getBlankOptionLabel()) {
            $field['eval']['blankOptionLabel'] = $this->get(TranslatorInterface::class)->trans($blankOptionLabel, [], 'isotope');
        }

        if ($this->isVariantOption()) {
            $field['eval']['includeBlankOption'] = true;
        }

        if (!isset($keys[$this->fieldName]) && $this->isFrontendFilterable() && !$this->isCustomerDefined()) {
            $keys[$this->fieldName] = 'index';
        }
    }

    protected function getSqlDefinition(): array|string
    {
        if ($this->hasMultiple()) {
            return ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false];
        }

        if (OptionsSource::Attribute === $this->getOptionsSource()) {
            return ['type' => Types::STRING, 'length' => 64, 'options' => ['default' => '']];
        }

        return ['type' => Types::INTEGER, 'options' => ['unsigned' => true, 'default' => 0]];
    }
}
