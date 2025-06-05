<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\StringUtil;
use Contao\Widget;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\Collection\AttributeOption as AttributeOptionCollection;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\AttributeOption;
use Isotope\Translation;

abstract class AbstractOptionsAttribute extends AbstractAttribute implements OptionsAttributeInterface
{
    private array $variantOptionsCache = [];

    private bool $variantOption = false;

    public function __construct(
        string $fieldName,
        protected OptionsSource|null $optionsSource,
        private readonly Connection $connection,
    ) {
        parent::__construct($fieldName);
    }

    public function getOptionsForVariants(array $variantIds, array $variantOptions = []): array
    {
        if (!$this instanceof VariantAttributeInterface) {
            return [];
        }

        if (0 === \count($variantIds)) {
            return [];
        }

        sort($variantIds);
        ksort($variantOptions);
        $strKey = md5(implode('-', $variantIds).'_'.json_encode($variantOptions));

        if (!isset($this->variantOptionsCache[$strKey])) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->select('DISTINCT '.$this->fieldName)
                ->from('tl_iso_product')
                ->where($qb->expr()->in('id', $qb->createNamedParameter($variantIds, ArrayParameterType::INTEGER)))
            ;

            foreach ($variantOptions as $field => $value) {
                $qb->andWhere($qb->expr()->eq($field, $qb->createNamedParameter($value)));
            }

            $this->variantOptionsCache[$strKey] = $qb->fetchFirstColumn();
        }

        return $this->variantOptionsCache[$strKey];
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

    public function prepareOptionsWizard(Widget $widget, array $columns): array
    {
        if ($this->isVariantOption()) {
            unset($columns['default'], $columns['group']);
        }

        return $columns;
    }

    public function getOptionsForWidget(IsotopeProduct|null $product = null): array
    {
        $arrOptions = [];

        switch ($this->optionsSource) {
            // Single checkbox in the backend does not have options
            case OptionsSource::Name:
                return [['value' => 1, 'label' => $this->getName()]];

            // @deprecated remove in Isotope 3.0
            case OptionsSource::Attribute:
                $options = StringUtil::deserialize($this->options);

                if (!empty($options) && \is_array($options)) {
                    if ($this->isCustomerDefined()) {
                        // Build for a frontend widget

                        foreach ($options as $option) {
                            $option['label'] = Translation::get($option['label']);

                            $arrOptions[] = $option;
                        }
                    } else {
                        // Build for a backend widget

                        $group = '';

                        foreach ($options as $option) {
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
                if (CompatibilityHelper::isFrontend() && !($product instanceof IsotopeProduct)) {
                    throw new \InvalidArgumentException('Must pass IsotopeProduct to Attribute::getOptions if optionsSource is "product"');
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

    public function getOptionsFromManager(IsotopeProduct|null $product = null): AttributeOptionCollection|null
    {
        switch ($this->optionsSource) {
            case OptionsSource::Table:
                return AttributeOption::findByAttribute($this);

            case OptionsSource::Product:
                if (CompatibilityHelper::isFrontend() && !($product instanceof IsotopeProduct)) {
                    throw new \InvalidArgumentException('Must pass IsotopeProduct to Attribute::getOptionsFromManager if optionsSource is "product"');
                }

                $productId = $product->getId();

                if (
                    $product->isVariant()
                    && !\in_array($this->getFieldName(), $product->getType()->getVariantAttributes(), true)
                ) {
                    $productId = $product->getProductId();
                }

                if (
                    !\is_array($this->varOptionsCache)
                    || !\array_key_exists($productId, $this->varOptionsCache)
                ) {
                    $this->varOptionsCache[$productId] = AttributeOption::findByProductAndAttribute(
                        $product,
                        $this,
                    );
                }

                return $this->varOptionsCache[$productId];

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

                return $this->connection->fetchAllAssociative(
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
}
