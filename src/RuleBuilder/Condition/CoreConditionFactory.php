<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Model;
use Isotope\CoreBundle\EventListener\DataContainer\Rule\LabelListener;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\ModelType\Exception\InvalidTypeException;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @implements ModelTypeFactoryInterface<RuleConditionInterface, RuleConditionModel>
 */
class CoreConditionFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(
        private readonly RuleMatcher $ruleMatcher,
        private readonly LabelListener $labelListener,
    ) {
    }

    /**
     * @param RuleConditionModel $model
     */
    public function create(Model $model, StoreContext $context): object
    {
        $this->validateInstanceOf($model, RuleConditionModel::class);

        return match ($model->type) {
            'nested_rule' => new NestedRuleCondition(
                $this->ruleMatcher,
                $this->labelListener,
                (int) $model->rule,
            ),

            default => throw new InvalidTypeException($model->type, self::class),
        };
    }
}
