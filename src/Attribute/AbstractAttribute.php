<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\CoreBundle\Attribute\Exception\AttributeException;
use Isotope\CoreBundle\ModelType\AbstractModelType;
use Isotope\CoreBundle\Product\CoreProduct;

abstract class AbstractAttribute extends AbstractModelType implements AttributeInterface
{
    private string|null $name = null;

    private string|null $description = null;

    private string $placeholder = '';

    private bool $customerDefined = false;

    private bool $mandatory = false;

    private bool $backendSearchable = false;

    private bool $backendFilterable = false;

    private bool $backendColumn = false;

    private bool $frontendSearchable = false;

    private bool $frontendFilterable = false;

    private bool $frontendSortable = false;

    private string|null $customTemplate = null;

    public function __construct(protected readonly string $fieldName)
    {
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getName(): string
    {
        return $this->name ?: $this->fieldName;
    }

    public function setName(string|null $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function setDescription(string|null $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function setCustomerDefined(bool $customerDefined): self
    {
        $this->customerDefined = $customerDefined;

        return $this;
    }

    public function isCustomerDefined(): bool
    {
        return $this->customerDefined;
    }

    public function isMandatory(): bool
    {
        return $this instanceof VariantAttributeInterface && $this->isVariantOption() ?: $this->mandatory;
    }

    public function setMandatory(bool $mandatory): self
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function isBackendFilterable(): bool
    {
        return $this->backendFilterable;
    }

    public function setBackendFilterable(bool $backendFilterable): self
    {
        $this->backendFilterable = $backendFilterable;

        return $this;
    }

    public function isBackendSearchable(): bool
    {
        return $this->backendSearchable;
    }

    public function setBackendSearchable(bool $backendSearchable): self
    {
        $this->backendSearchable = $backendSearchable;

        return $this;
    }

    public function isBackendColumn(): bool
    {
        return $this->backendColumn;
    }

    public function setBackendColumn(bool $backendColumn): self
    {
        $this->backendColumn = $backendColumn;

        return $this;
    }

    public function isFrontendSearchable(): bool
    {
        return $this->frontendSearchable;
    }

    public function setFrontendSearchable(bool $frontendSearchable): self
    {
        $this->frontendSearchable = $frontendSearchable;

        return $this;
    }

    public function isFrontendFilterable(): bool
    {
        return $this->frontendFilterable;
    }

    public function setFrontendFilterable(bool $frontendFilterable): self
    {
        $this->frontendFilterable = $frontendFilterable;

        return $this;
    }

    public function isFrontendSortable(): bool
    {
        return $this->frontendSortable;
    }

    public function setFrontendSortable(bool $frontendSortable): self
    {
        $this->frontendSortable = $frontendSortable;

        return $this;
    }

    public function getCustomTemplate(): string|null
    {
        return $this->customTemplate;
    }

    public function setCustomTemplate(string|null $customTemplate): self
    {
        $this->customTemplate = $customTemplate;

        return $this;
    }

    public function getInputType(): string|null
    {
        foreach ($this->guessInputType() as $name) {
            if (isset($GLOBALS['BE_FFL'][$name])) {
                return $name;
            }
        }

        throw AttributeException::guessInputType(static::class, __METHOD__);
    }

    public function getFrontendWidget(): string|null
    {
        foreach ($this->guessInputType() as $name) {
            if (isset($GLOBALS['TL_FFL'][$name])) {
                return $GLOBALS['TL_FFL'][$name];
            }
        }

        throw AttributeException::guessFrontendWidget(static::class, __METHOD__);
    }

    public function getValue(CoreProduct $product): mixed
    {
        return $product->{$this->fieldName};
    }

    public function updateDcaField(array &$field, array &$keys): void
    {
        if (null !== $this->name && null !== $this->description) {
            $field['label'] = [$this->name, $this->description];
        }

        $field['inputType'] = $this->isCustomerDefined() ? '' : $this->getInputType();
        $field['eval'] ??= [];

        if ('' !== $this->placeholder) {
            $field['eval']['placeholder'] = $this->placeholder;
        }

        $field['filter'] = $this->isBackendFilterable() && !$this->isCustomerDefined();
        $field['search'] = $this->isBackendSearchable() && !$this->isCustomerDefined();
        $field['attributes']['fe_filter'] = $this->isFrontendFilterable();
        $field['attributes']['fe_sorting'] = $this->isFrontendSortable();
        $field['attributes']['fe_search'] = $this->isFrontendSearchable();
        $field['eval']['mandatory'] = $this->isMandatory();

        if (!isset($field['sql']) && !$this->isCustomerDefined() && null !== ($sql = $this->getSqlDefinition())) {
            $field['sql'] = $sql;
        }
    }

    abstract protected function getSqlDefinition(): array|string|null;

    private function guessInputType(): array
    {
        $name = ltrim(strrchr(static::class, '\\'), '\\');

        if (str_ends_with($name, ucfirst('Attribute'))) {
            $name = substr($name, 0, \strlen('Attribute') * -1);
        }

        return [$name, lcfirst($name), strtolower($name)];
    }
}
