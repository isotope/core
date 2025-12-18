<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RuleCondition;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_rule_condition', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly RuleConditionRegistry $conditionRegistry,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(array $row): string
    {
        $type = $this->translator->trans('MODEL.tl_iso_rule_condition.'.$row['type'].'.0', [], 'contao_default');
        $condition = $this->conditionRegistry->get(RuleCondition::findById($row['id']));

        if (!$condition) {
            return $type;
        }

        return sprintf('<strong>%s:</strong> %s', $type, $condition->getBackendLabel());
    }
}
