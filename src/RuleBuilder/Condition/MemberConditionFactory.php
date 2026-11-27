<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\ModelType\Exception\InvalidTypeException;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ModelTypeFactoryInterface<RuleConditionInterface, RuleConditionModel>
 */
class MemberConditionFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param RuleConditionModel $model
     */
    public function create(Model $model, StoreContext $context): object
    {
        $this->validateInstanceOf($model, RuleConditionModel::class);

        return match ($model->type) {
            'members' => new MembersCondition(
                $this->framework,
                $this->translator,
                $model->operator,
                array_map(intval(...), explode(',', (string) $model->members)),
            ),
            'member_groups' => new MemberGroupsCondition(
                $this->security,
                $this->translator,
                $model->operator,
                array_map(intval(...), explode(',', (string) $model->member_groups)),
            ),
            'member_authenticated' => new MemberAuthenticatedCondition(
                $this->security,
                $this->translator,
                $model->operator,
            ),
            default => throw new InvalidTypeException($model->type, self::class),
        };
    }
}
