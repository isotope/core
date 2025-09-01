<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\StringUtil;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Isotope\Collection\AttributeOption as AttributeOptionCollection;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\Model\AttributeOption;
use Isotope\Model\Product;
use Isotope\Translation;

abstract class AbstractOptionsAttribute extends AbstractAttribute implements OptionsAttributeInterface, ContainerAwareAttributeInterface
{
    private bool $variantOption = false;

    private array|null $options = null;

    private string|null $foreignKey = null;

    private bool $multiple = false;

    public function __construct(
        string $fieldName,
        protected OptionsSource|null $optionsSource,
    ) {
        parent::__construct($fieldName);
    }

    public static function getSubscribedServices(): array
    {
        return [Connection::class];
    }

    public function isVariantOption(): bool
    {
        return $this instanceof VariantAttributeInterface && $this->variantOption;
    }

    public function setVariantOption(bool $variantOption): self
    {
        $this->variantOption = $variantOption;

        return $this;
    }

    public function getOptionsSource(): OptionsSource|null
    {
        return $this->optionsSource;
    }

    public function setOptions(array|null $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array|null
    {
        return $this->options;
    }

    public function getForeignKey(): string|null
    {
        return $this->foreignKey;
    }

    public function setForeignKey(string|null $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function hasMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getOptionsForWidget(ProductInterface|null $product = null): array
    {
        $arrOptions = [];

        switch ($this->optionsSource) {
            // Single checkbox in the backend does not have options
            case OptionsSource::Name:
                return [['value' => 1, 'label' => $this->getName()]];

            case OptionsSource::Attribute:
                if (empty($this->options)) {
                    return [];
                }

                if ($this->isCustomerDefined()) {
                    // Build for a frontend widget

                    foreach ($this->options as $option) {
                        $option['label'] = Translation::get($option['label']);

                        $arrOptions[] = $option;
                    }
                } else {
                    // Build for a backend widget

                    $group = '';

                    foreach ($this->options as $option) {
                        $option['label'] = Translation::get($option['label']);

                        if ($option['group'] ?? false) {
                            $group = $option['label'];
                            continue;
                        }

                        if ('' !== $group) {
                            $arrOptions[$group][] = $option;
                        } else {
                            $arrOptions[] = $option;
                        }
                    }
                }
                break;

            case OptionsSource::Table:
                $objOptions = $this->getOptionsFromManager();

                if (null === $objOptions) {
                    $arrOptions = [];
                } elseif ($this->isCustomerDefined()) {
                    $arrOptions = $objOptions->getArrayForFrontendWidget($product, CompatibilityHelper::isFrontend());
                } else {
                    $arrOptions = $objOptions->getArrayForBackendWidget();
                }
                break;

            case OptionsSource::Product:
                if (CompatibilityHelper::isFrontend() && !($product instanceof ProductInterface)) {
                    throw new \InvalidArgumentException('Must pass product to Attribute::getOptions if optionsSource is "product"');
                }

                $objOptions = $this->getOptionsFromManager($product);

                if (null === $objOptions) {
                    return [];
                }

                return $objOptions->getArrayForFrontendWidget($product, CompatibilityHelper::isFrontend());

            default:
                $config = Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$this->getFieldName()], $this->getFieldName());

                if (!isset($config['options']) || !\is_array($config['options'])) {
                    throw new \UnexpectedValueException('Invalid options source "'.$this->optionsSource->value.'" for '.Attribute::getTable().'.'.$this->getFieldName());
                }

                return $config['options'];
        }

        // Variant options cannot have a default value (see #1546)
        if ($this->isVariantOption()) {
            foreach ($arrOptions as &$option) {
                $option['default'] = '';
            }
        }

        return $arrOptions;
    }

    public function getOptionsFromManager(ProductInterface|null $product = null): AttributeOptionCollection|null
    {
        switch ($this->optionsSource) {
            case OptionsSource::Table:
                return AttributeOption::findByAttribute($this);

            case OptionsSource::Product:
                if (null === $product && CompatibilityHelper::isFrontend()) {
                    throw new \InvalidArgumentException('Must pass product to Attribute::getOptionsFromManager if optionsSource is "product"');
                }

                return AttributeOption::findByProductAndAttribute(
                    $product,
                    $this,
                );

            default:
                throw new \UnexpectedValueException(Attribute::getTable().'.'.$this->getFieldName().' does not use options manager');
        }
    }

    public function getOptionsForProductFilter(array $values): array
    {
        switch ($this->optionsSource) {
            case OptionsSource::Name:
                $arrOptions = [];

                if (\array_key_exists('1', $values)) {
                    $arrOptions['1'] = $GLOBALS['TL_LANG']['MSC']['yes'];
                }

                if (\array_key_exists('', $values)) {
                    $arrOptions[''] = $GLOBALS['TL_LANG']['MSC']['no'];
                }

                return $arrOptions;

            case OptionsSource::Attribute:
                $arrOptions = [];
                $options = StringUtil::deserialize($this->options);

                if (!empty($options) && \is_array($options)) {
                    foreach ($options as $option) {
                        if (\in_array($option['value'], $values, true)) {
                            $option['label'] = Translation::get($option['label']);
                            $arrOptions[] = $option;
                        }
                    }
                }

                return $arrOptions;

            case OptionsSource::ForeignKey:
                $foreignKey = $this->parseForeignKey($this->foreignKey, $GLOBALS['TL_LANGUAGE']);
                [$table, $field] = explode('.', $foreignKey, 2);

                return $this->get(Connection::class)->fetchAllAssociative(
                    "SELECT id AS value, $field AS label FROM $table WHERE id IN (".implode(',', $values).') ORDER BY label',
                );

            case OptionsSource::Table:
            case OptionsSource::Product:
                $objOptions = AttributeOption::findPublishedByIds($values);

                return null === $objOptions ? [] : $objOptions->getArrayForFrontendWidget(null, false);

            default:
                $config = Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$this->getFieldName()], $this->getFieldName());

                if (!isset($config['options']) || !\is_array($config['options'])) {
                    throw new \UnexpectedValueException('Invalid options source "'.$this->optionsSource->value.'" for '.Attribute::getTable().'.'.$this->getFieldName());
                }

                return array_filter($config['options'], static fn ($option) => \in_array($option['value'], $values, false));
        }
    }

    public function canHavePrices(): bool
    {
        if ($this instanceof VariantAttributeInterface && $this->isVariantOption()) {
            return false;
        }

        return true;
        // TODO: previously checked the database!
        // return \in_array($this->field_name, Attribute::getPricedFields(), true);
    }

    protected function parseForeignKey($strSettings, $strLanguage = false)
    {
        $strFallback = null;
        $arrLines    = StringUtil::trimsplit('@\r\n|\n|\r@', $strSettings);

        // Return false if there are no lines
        if ($strSettings == '' || !\is_array($arrLines) || empty($arrLines)) {
            return null;
        }

        // Loop over the lines
        foreach ($arrLines as $strLine) {
            // Ignore empty lines and comments
            if ($strLine == '' || strpos($strLine, '#') === 0) {
                continue;
            }

            // Check for a language1
            if (preg_match('/^([a-z]{2}(-[A-Z]{2})?)=(.+)$/', $strLine, $matches)) {
                $foreignKey = $matches[3];

                if ($matches[1] === $strLanguage) {
                    return $foreignKey;
                }

                if (null === $strFallback) {
                    $strFallback = $foreignKey;
                }
            } elseif (null === $strFallback) {
                // The row without language is the fallback
                $strFallback = $strLine;
            }
        }

        return $strFallback;
    }
}
