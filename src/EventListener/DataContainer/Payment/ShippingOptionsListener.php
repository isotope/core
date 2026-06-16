<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Payment;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_payment', 'fields.shipping_modules.options')]
class ShippingOptionsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): array
    {
        $groupLabel = $this->translator->trans('IMD.shipping.0', [], 'contao_modules');

        return [
            -1 => $this->translator->trans('tl_iso_payment.no_shipping', [], 'contao_tl_iso_payment'),
            $groupLabel => $this->connection->fetchAllKeyValue('SELECT id, name FROM tl_iso_shipping ORDER BY name'),
        ];
    }
}
