<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\MemberGroupModel;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'member', factory: MemberConditionFactory::class)]
class MemberGroupCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly array $groups,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(): bool
    {
        if ('has_all' === $this->operator) {
            foreach ($this->groups as $group) {
                if (!$this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, [$group])) {
                    return false;
                }
            }

            return true;
        }

        $hasGroups = $this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, [$this->groups]);

        return match ($this->operator) {
            'has_one' => $hasGroups,
            'has_none' => !$hasGroups,
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        $names = MemberGroupModel::findMultipleByIds($this->groups)?->fetchEach('name') ?? [];
        $groups = array_replace(array_combine($this->groups, $this->groups), $names);

        return sprintf('%s "%s"', $this->trans($this->operator), implode('", "', $groups));
    }

    public function getOperators(): array
    {
        $op = ['has_all', 'has_one', 'has_none'];

        return array_combine($op, array_map($this->trans(...), $op));
    }

    private function trans(string $id): string
    {
        return $this->translator->trans('tl_iso_rule_condition.operator.'.$id, [], 'contao_tl_rule_condition');
    }
}
