<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\OrderStatus;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_iso_orderstatus', 'fields.workflow.options')]
class WorkflowOptionsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(DataContainer $dc): array
    {
        $currentRecord = $dc->getCurrentRecord();

        if (empty($currentRecord['type'])) {
            return [];
        }

        return $this->connection->fetchAllKeyValue(
            'SELECT alias, name FROM tl_iso_orderstatus WHERE type=? AND id!=?',
            [$currentRecord['type'], $dc->id],
        );
    }
}
