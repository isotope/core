<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\EventListener\DataContainer\Rule\LabelListener;
use Isotope\CoreBundle\Model\RuleModel;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;

#[AsRuleCondition('nested_rule', category: '', factory: CoreConditionFactory::class, priority: 100)]
class NestedRuleCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly RuleMatcher $ruleMatcher,
        private readonly LabelListener $labelListener,
        private readonly int $rule,
    ) {
    }

    public function isValid(): bool
    {
        return $this->rule > 0;
    }

    public function isApplicable(StoreContext $context): bool
    {
        return $this->ruleMatcher->isApplicable($this->rule, $context);
    }

    public function getBackendLabel(): string
    {
        $ruleModel = RuleModel::findById($this->rule);

        if (!$ruleModel) {
            return '-';
        }

        return $this->labelListener->__invoke($ruleModel->row(), $ruleModel->name);
    }

    public function getOperators(): array
    {
        return [];
    }
}
