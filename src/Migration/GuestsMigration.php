<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\Version500\AbstractGuestsMigration;

class GuestsMigration extends AbstractGuestsMigration
{
    protected function getTables(): array
    {
        return [
            'tl_iso_shipping',
            'tl_iso_payment',
            'tl_iso_product',
            'tl_iso_tax_rate',
        ];
    }
}
