<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Weight\Weight;
use Isotope\CoreBundle\Util\FloatComparator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'cart', factory: CartConditionFactory::class)]
class CartWeightCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly Weight|null $weight,
        private readonly Weight|null $from,
        private readonly Weight|null $to,
    ) {
    }

    public function isValid(): bool
    {
        if ('range' === $this->operator) {
            return null !== $this->from
                && null !== $this->to
                && FloatComparator::lessThanOrEquals($this->from->getAmount(), $this->to->getAmountInUnit($this->from->getUnit()));
        }

        return null !== $this->weight && \in_array($this->operator, ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'], true);
    }

    public function isApplicable(StoreContext $context): bool
    {
        $cart = $context->getCart();
        $reference = 'range' === $this->operator ? $this->from : $this->weight;

        if (null === $cart || !$this->isValid()) {
            return false;
        }

        $unit = $reference->getUnit();
        $actual = $cart->getLineItems()->products()->calculateWeight($unit)->getAmount();
        $threshold = $reference->getAmount();

        return match ($this->operator) {
            'eq' => FloatComparator::equals($actual, $threshold),
            'neq' => FloatComparator::notEquals($actual, $threshold),
            'gt' => FloatComparator::greaterThan($actual, $threshold),
            'gte' => FloatComparator::greaterThanOrEquals($actual, $threshold),
            'lt' => FloatComparator::lessThan($actual, $threshold),
            'lte' => FloatComparator::lessThanOrEquals($actual, $threshold),
            'range' => FloatComparator::greaterThanOrEquals($actual, $threshold) && FloatComparator::lessThanOrEquals($actual, $this->to->getAmountInUnit($unit)),
            default => false,
        };
    }

    public function getBackendLabel(): string
    {
        if (!$this->isValid()) {
            return '-';
        }

        if ('range' === $this->operator) {
            return $this->translator->trans('tl_iso_rule_condition.range', [(string) $this->from, (string) $this->to], 'contao_tl_iso_rule_condition');
        }

        return $this->trans($this->operator).' '.$this->weight;
    }

    public function getOperators(): array
    {
        $op = ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'range'];

        return array_combine($op, array_map($this->trans(...), $op));
    }

    private function trans(string $id): string
    {
        return $this->translator->trans('tl_iso_rule_condition.operator.'.$id, [], 'contao_tl_rule_condition');
    }
}
