<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'member', factory: MemberConditionFactory::class)]
class MemberAuthenticatedCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $isGranted = $this->security->isGranted('ROLE_MEMBER');

        return match ($this->operator) {
            'logged_in' => $isGranted,
            'not_logged_in' => !$isGranted,
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        return match ($this->operator) {
            'logged_in' => $this->translator->trans('tl_iso_rule_condition.operator.logged_in', [], 'contao_tl_iso_rule_condition'),
            'not_logged_in' => $this->translator->trans('tl_iso_rule_condition.operator.not_logged_in', [], 'contao_tl_iso_rule_condition'),
        };
    }

    public function getOperators(): array
    {
        return [
            'logged_in' => $this->translator->trans('tl_iso_rule_condition.operator.logged_in', [], 'contao_tl_iso_rule_condition'),
            'not_logged_in' => $this->translator->trans('tl_iso_rule_condition.operator.not_logged_in', [], 'contao_tl_iso_rule_condition'),
        ];
    }
}
