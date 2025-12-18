<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
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

    public function isApplicable(): bool
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
        $op = ['logged_in', 'not_logged_in'];

        return array_combine($op, array_map($this->trans(...), $op));
    }

    private function trans(string $id): string
    {
        return $this->translator->trans('tl_iso_rule_condition.operator.'.$id, [], 'contao_tl_rule_condition');
    }
}
