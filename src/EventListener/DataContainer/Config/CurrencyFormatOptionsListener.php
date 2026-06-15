<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Config;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCallback('tl_iso_config', 'fields.currencyFormat.options')]
class CurrencyFormatOptionsListener
{
    public function __construct(
        #[Autowire(param: 'isotope.currency_formats')] private readonly array $currencyFormats,
    ) {
    }

    public function __invoke(DataContainer $dc): array
    {
        return array_keys($this->currencyFormats);
    }
}
