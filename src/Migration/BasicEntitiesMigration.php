<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\Version500\AbstractBasicEntitiesMigration;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class BasicEntitiesMigration extends AbstractBasicEntitiesMigration
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct($this->connection);
    }

    protected function getDatabaseColumns(): array
    {
        $mapping = [
            ['tl_iso_config', 'label'],

            ['tl_iso_payment', 'label'],
            ['tl_iso_payment', 'note'],

            ['tl_iso_shipping', 'label'],
            ['tl_iso_shipping', 'note'],

            ['tl_iso_product', 'name'],
            ['tl_iso_product', 'sku'],
            ['tl_iso_product', 'teaser'],
            ['tl_iso_product', 'description'],
            ['tl_iso_product', 'meta_title'],
            ['tl_iso_product', 'meta_description'],
        ];

        $attributes = $this->connection->fetchFirstColumn("SELECT field_name FROM tl_iso_attribute WHERE type NOT IN ('radio', 'checkbox', 'select', 'mediaManager')");

        foreach ($attributes as $attribute) {
            $mapping[] = ['tl_iso_attribute', $attribute];
        }

        return $mapping;
    }
}
