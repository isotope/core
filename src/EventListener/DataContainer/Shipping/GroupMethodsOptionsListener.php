<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Shipping;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_iso_shipping', 'fields.group_methods.options')]
class GroupMethodsOptionsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(DataContainer $dc): array
    {
        if (!$dc->id) {
            return $this->connection->fetchAllKeyValue('SELECT id, name FROM tl_iso_shipping');
        }

        return $this->connection->fetchAllKeyValue(
            'SELECT id, name FROM tl_iso_shipping WHERE id!=?',
            [$dc->id],
        );
    }
}
