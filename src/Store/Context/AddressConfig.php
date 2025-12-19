<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

final class AddressConfig
{
    public function __construct(
        private array $addressFields,
        private array $addressFieldsConfig,
        private array $countryCodes,
        private string $defaultCountryCode,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAddressFields(): array
    {
        return $this->addressFields;
    }

    /**
     * @param array<string> $addressFields
     */
    public function setAddressFields(array $addressFields): self
    {
        $this->addressFields = $addressFields;

        return $this;
    }

    /**
     * @return list<array{
     *     value: string,
     *     enabled: bool,
     *     mandatory: bool,
     * }>
     */
    public function getAddressFieldsConfig(): array
    {
        return $this->addressFieldsConfig;
    }

    /**
     * @param list<array{
     *     value: string,
     *     enabled: bool,
     *     mandatory: bool,
     * }> $addressFieldsConfig
     */
    public function setAddressFieldsConfig(array $addressFieldsConfig): self
    {
        $this->addressFieldsConfig = $addressFieldsConfig;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getCountryCodes(): array
    {
        return $this->countryCodes;
    }

    /**
     * @param array<string> $countryCodes
     */
    public function setCountryCodes(array $countryCodes): self
    {
        $this->countryCodes = $countryCodes;

        return $this;
    }

    public function getDefaultCountryCode(): string
    {
        return $this->defaultCountryCode;
    }

    public function setDefaultCountryCode(string $defaultCountryCode): self
    {
        $this->defaultCountryCode = $defaultCountryCode;

        return $this;
    }
}
