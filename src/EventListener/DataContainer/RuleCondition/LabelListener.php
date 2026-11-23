<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RuleCondition;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\ModelType\Exception\FactoryNotFoundException;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_rule_condition', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly RuleConditionRegistry $conditionRegistry,
        private readonly TranslatorInterface $translator,
        private readonly StoreContextFactory $contextFactory,
    ) {
    }

    public function __invoke(array $row): string
    {
        $type = $this->translator->trans('MODEL.tl_iso_rule_condition.'.$row['type'].'.0', [], 'contao_default');

        try {
            $condition = $this->conditionRegistry->get(RuleConditionModel::findById($row['id']), $this->contextFactory->createBackend());
        } catch (FactoryNotFoundException) {
            $condition = null;
        }

        if (!$condition || !$condition->isValid()) {
            return $this->translator->trans('ERR.invalidRuleCondition', [$row['type']], 'contao_default');
        }

        return \sprintf('<strong>%s:</strong> %s', $type, $condition->getBackendLabel());
    }
}
