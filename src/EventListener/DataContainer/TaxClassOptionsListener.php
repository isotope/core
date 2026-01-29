<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_payment', 'fields.tax_class.options')]
#[AsCallback('tl_iso_shipping', 'fields.tax_class.options')]
class TaxClassOptionsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): array
    {
        $groupLabel = $this->translator->trans('IMD.tax_class.0', [], 'contao_modules');

        return [
            -1 => $this->translator->trans('MSC.splittedTaxRate', [], 'contao_default'),
            $groupLabel => $this->connection->fetchAllKeyValue("SELECT id, name FROM tl_iso_tax_class ORDER BY name"),
        ];
    }
}
