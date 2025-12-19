<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Model;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidTypeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ModelTypeFactoryInterface<RuleConditionInterface, RuleCondition>
 */
class MemberConditionFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param RuleCondition $model
     */
    public function create(Model $model): object
    {
        $this->validateInstanceOf($model, RuleCondition::class);

        return match ($model->type) {
            'member_group' => new MemberGroupCondition($this->security, $this->translator, $model->operator, explode(',', (string) $model->member_groups)),
            'member_authenticated' => new MemberAuthenticatedCondition($this->security, $this->translator, $model->operator),
            default => throw new InvalidTypeException($model->type, self::class),
        };
    }
}
