<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder;

use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\Model\RuleModel;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\Store\Context\StoreContext;

class RuleMatcher
{
    public function __construct(private readonly RuleConditionRegistry $conditionRegistry)
    {
    }

    public function isApplicable(int $ruleId, StoreContext $context): bool
    {
        $rule = RuleModel::findById($ruleId);

        if (!$rule?->enabled) {
            return false;
        }

        $conditions = RuleConditionModel::findByRuleId($ruleId);

        if (null === $conditions) {
            return true;
        }

        return $this->validateConditions($conditions->getModels(), $context);
    }

    /**
     * @param array<RuleConditionModel> $conditions
     */
    private function validateConditions(array $conditions, StoreContext $context): bool
    {
        foreach ($conditions as $model) {
            try {
                $condition = $this->conditionRegistry->get($model, $context);
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
