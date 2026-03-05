<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

use Contao\CoreBundle\Intl\Countries;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\Config;

class AddressConfigFactory
{
    public function __construct(
        private readonly Countries $countries,
    ) {
    }

    public function createFromConfig(Config $config, string $type): AddressConfig
    {
        return $this->create(
            $config->address_fields,
            $type,
            (string) $config->{$type.'_countries'},
            $config->{$type.'_country'},
        );
    }

    public function create(array|string $fieldConfig, string $type, string $countries, string $defaultCountry): AddressConfig
    {
        $addressFields = [];
        $mandatoryFields = [];

        $fields = StringUtil::deserialize($fieldConfig);

        if (\is_array($fields)) {
            foreach ($fields as $field) {
                if ('disabled' === $field[$type]) {
                    continue;
                }

                $addressFields[] = $field['name'];

                if ('mandatory' === $field[$type]) {
                    $mandatoryFields[] = $field['name'];
                }
            }
        }

        if ('' === $countries) {
            $countryCodes = array_keys($this->countries->getCountries());
        } else {
            $countryCodes = explode(',', $countries);
        }

        return new AddressConfig(
            $addressFields,
            $mandatoryFields,
            $countryCodes,
            $defaultCountry,
        );
    }
}
