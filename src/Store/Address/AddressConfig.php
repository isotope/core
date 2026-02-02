<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

final readonly class AddressConfig
{
    public function __construct(
        private array $fields,
        private array $mandatory,
        private array $countryCodes,
        private string $defaultCountryCode,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return list<string>
     */
    public function getMandatory(): array
    {
        return $this->mandatory;
    }

    public function isEnabled(string $field): bool
    {
        return \in_array($field, $this->fields, true);
    }

    public function isMandatory(string $field): bool
    {
        return \in_array($field, $this->mandatory, true);
    }

    /**
     * @return array<string>
     */
    public function getCountryCodes(): array
    {
        return $this->countryCodes;
    }

    public function getDefaultCountryCode(): string
    {
        return $this->defaultCountryCode;
    }

    public function mergeWith(self $addressConfig): self
    {
        return new self(
            array_unique(array_merge($this->fields, $addressConfig->getFields())),
            array_intersect($this->mandatory, $addressConfig->getMandatory()),
            array_unique(array_merge($this->countryCodes, $addressConfig->getCountryCodes())),
            $this->defaultCountryCode,
        );
    }
}
