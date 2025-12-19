<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

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

    public function setVariantOption(bool $variantOption): self
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

    public function updateDcaField(array &$field, array &$keys): void
    {
        parent::updateDcaField($field, $keys);

        $field['options_callback'] = fn (mixed $dc) => $this->getOptions($dc instanceof ProductContext ? $dc : null)->asDcaArray();
    }
}
