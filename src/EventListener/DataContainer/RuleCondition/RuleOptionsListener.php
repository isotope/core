<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RuleCondition;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_iso_rule_condition', 'fields.rule.options')]
class RuleOptionsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(DataContainer $dc): array
    {
        return $this->connection->fetchAllKeyValue('SELECT id, name FROM tl_iso_rule WHERE id!=?', [(int) $dc->id]);
    }
}
