<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder;

use Contao\CoreBundle\Framework\ContaoFramework;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\Model\RuleModel;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\Store\Context\StoreContext;

class RuleMatcher
{
    private array $evaluatingRules = [];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RuleConditionRegistry $conditionRegistry,
    ) {
    }

    public function isApplicable(int $ruleId, StoreContext $context): bool
    {
        if (isset($this->evaluatingRules[$ruleId])) {
            return false;
        }

        $this->evaluatingRules[$ruleId] = true;

        try {
            $rule = $this->framework->getAdapter(RuleModel::class)->findById($ruleId);

            if (!$rule?->enabled) {
                return false;
            }

            $conditions = $this->framework->getAdapter(RuleConditionModel::class)->findByRuleId($ruleId);

            if (null === $conditions) {
                return true;
            }

            return $this->validateConditions($conditions->getModels(), $context);
        } finally {
            unset($this->evaluatingRules[$ruleId]);
        }
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
