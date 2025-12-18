<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\Intl\Currencies;

#[AsCallback('tl_iso_config', 'fields.currency.options')]
#[AsCallback('tl_iso_rule_condition', 'fields.currencies.options')]
class CurrencyOptionsListener
{
    public function __invoke(): array
    {
        $currencies = Currencies::getNames();

        foreach ($currencies as $code => &$name) {
            $name = $code.' - '.$name;
        }

        return $currencies;
    }
}
