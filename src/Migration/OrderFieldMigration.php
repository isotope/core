<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\Version500\AbstractOrderFieldMigration;

/**
 * @internal
 */
class OrderFieldMigration extends AbstractOrderFieldMigration
{
    protected function getTableFields(): array
    {
        return [
            'tl_iso_related_product' => [
                'productsOrder' => 'products',
            ],
        ];
    }
}
