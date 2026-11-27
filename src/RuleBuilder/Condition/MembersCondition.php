<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\MemberModel;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'member', factory: MemberConditionFactory::class)]
class MembersCondition implements RuleConditionInterface
{
    /**
     * @param list<int> $members
     */
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly array $members,
    ) {
    }

    public function isValid(): bool
    {
        return [] !== $this->members && \in_array($this->operator, ['has_one', 'has_none'], true);
    }

    public function isApplicable(StoreContext $context): bool
    {
        $memberId = $context->getMemberId();

        if (!$memberId || !$this->isValid()) {
            return false;
        }

        return \in_array($memberId, $this->members, true) === ('has_one' === $this->operator);
    }

    public function getBackendLabel(): string
    {
        if (!$this->isValid()) {
            return '-';
        }

        $names = $this->framework->getAdapter(MemberModel::class)->findMultipleByIds($this->members)?->fetchEach('username') ?? [];
        $names = array_replace(array_combine($this->members, $this->members), $names);

        return \sprintf('%s "%s"', $this->trans($this->operator), htmlspecialchars(implode('", "', $names)));
    }

    public function getOperators(): array
    {
        $op = ['has_one', 'has_none'];

        return array_combine($op, array_map($this->trans(...), $op));
    }

    private function trans(string $id): string
    {
        return $this->translator->trans('tl_iso_rule_condition.operator.'.$id, [], 'contao_tl_rule_condition');
    }
}
