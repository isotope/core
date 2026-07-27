<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Controller;
use Contao\System;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\Store\Context\ProductContext;

abstract class AbstractOptionsAttribute extends AbstractAttribute implements OptionsAttributeInterface, ContainerAwareInterface
{
    private bool $variantOption = false;

    public function __construct(
        string $fieldName,
        private readonly OptionsSource|null $optionsSource,
        private readonly \Closure $optionsFactory,
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

    public function setVariantOption(bool $variantOption): static
    {
        $this->variantOption = $variantOption;

        return $this;
    }

    public function getOptionsSource(): OptionsSource|null
    {
        return $this->optionsSource;
    }

    public function getOptions(ProductContext|null $productContext = null): OptionCollection
    {
        $factory = $this->optionsFactory;

        return $factory($this, $productContext);
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

    #[\Override]
    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        $field['options_callback'] = fn (mixed $dc) => $this->getOptions($dc instanceof ProductContext ? $dc : null)->asDcaArray();

        if (
            $this->isBackendFilterable()
            && OptionsSource::Table === $this->optionsSource
        ) {
            $field['foreignKey'] = 'tl_iso_attribute_option.label';
        }

        if (OptionsSource::Product === $this->optionsSource && $this->isCustomerDefined()) {
            Controller::loadDataContainer('tl_iso_attribute');
            System::loadLanguageFile('tl_iso_attribute');

            $fieldTemplate = $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['optionsTable'];
            unset($fieldTemplate['label']);

            $field = array_replace_recursive($field, $fieldTemplate);

            $field['attributes']['dynamic'] = true;
            $field['foreignKey'] = 'tl_iso_attribute_option.label';
            $field['eval']['whereCondition'] = "field_name='{$this->getFieldName()}'";
        }
    }
}
