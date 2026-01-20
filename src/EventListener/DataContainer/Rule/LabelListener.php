<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Rule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\System;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\EventListener\DataContainer\RuleCondition\LabelListener as RuleConditionLabelListener;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_rule', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RuleConditionLabelListener $labelListener,
    ) {
    }

    public function __invoke(array $row, string $label, DataContainer $dc): string
    {
        System::loadLanguageFile('tl_iso_rule_condition');

        $conditions = [];
        $results = $this->connection->fetchAllAssociative("SELECT * FROM tl_iso_rule_condition WHERE pid=?", [$row['id']]);

        foreach ($results as $result){
            $conditions[] = $this->labelListener->__invoke($result);
        }

        if ([] === $conditions) {
            return $label;
        }

        return $label.'<br><ul class="iso_rule_conditions"><li>'.implode('</li><li>', $conditions).'</li></ul>';
    }
}
