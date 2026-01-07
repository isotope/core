<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Intl\Countries;

#[AsCallback('tl_iso_config', 'fields.country.options')]
#[AsCallback('tl_iso_config', 'fields.billing_country.options')]
#[AsCallback('tl_iso_config', 'fields.shipping_country.options')]
#[AsCallback('tl_iso_config', 'fields.billing_countries.options')]
#[AsCallback('tl_iso_config', 'fields.shipping_countries.options')]
#[AsCallback('tl_iso_payment', 'fields.countries.options')]
#[AsCallback('tl_iso_shipping', 'fields.countries.options')]
#[AsCallback('tl_iso_tax_rate', 'fields.countries.options')]
#[AsCallback('tl_iso_rule_condition', 'fields.countries.options')]
#[AsCallback('tl_iso_address', 'fields.country.options')]
class CountryOptionsListener
{
    public function __construct(private readonly Countries $countries)
    {
    }

    public function __invoke(): array
    {
        return $this->countries->getCountries();
    }
}
