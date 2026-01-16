<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder;

use Isotope\CoreBundle\Model\Rule;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;

class RuleMatcher
{
    public function __construct(
        private readonly RuleConditionRegistry $conditionRegistry,
    ) {
    }

    public function isApplicable(int $ruleId, StoreContext $context): bool
    {
        $rule = Rule::findById($ruleId);

        if (!$rule?->enabled) {
            return false;
        }

        $conditions = RuleCondition::findByRuleId($ruleId);

        if (null === $conditions) {
            return true;
        }

        return $this->validateConditions($conditions->getModels(), $context);
    }

    /**
     * @param array<RuleCondition> $conditions
     */
    private function validateConditions(array $conditions, StoreContext $context): bool
    {
        foreach ($conditions as $model) {
            try {
                $condition = $this->conditionRegistry->get($model);
            } catch (\Exception) {
                // Do not break the store if the rule cannot be loaded
                // TODO: log error
                return false;
            }

            if (!$condition->isValid() || !$condition->isApplicable($context)) {
                return false;
            }
        }

        return true;
    }
}
