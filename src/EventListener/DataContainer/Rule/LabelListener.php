<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Rule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\System;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\EventListener\DataContainer\RuleCondition\LabelListener as RuleConditionLabelListener;

#[AsCallback('tl_iso_rule', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RuleConditionLabelListener $labelListener,
    ) {
    }

    public function __invoke(array $row, string $label): string
    {
        System::loadLanguageFile('tl_iso_rule_condition');

        $conditions = [];
        $results = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_rule_condition WHERE pid=? AND tstamp>0', [$row['id']]);

        foreach ($results as $result) {
            $listener = $this->labelListener;
            $conditions[] = $listener($result);
        }

        if ([] === $conditions) {
            return $label;
        }

        return $label.'<br><ul class="iso_rule_conditions"><li>'.implode('</li><li>', $conditions).'</li></ul>';
    }
}
