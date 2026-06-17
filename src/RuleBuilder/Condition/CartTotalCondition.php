<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\FloatComparator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'cart', factory: CartConditionFactory::class)]
class CartTotalCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly float $amount,
        private readonly float $amountFrom,
        private readonly float $amountTo,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $cart = $context->getCart();
        $price = (float) $cart?->getTotal()?->getUnitPrice();

        return match ($this->operator) {
            'eq' => FloatComparator::equals($price, $this->amount),
            'neq' => FloatComparator::notEquals($price, $this->amount),
            'gt' => FloatComparator::greaterThan($price, $this->amount),
            'gte' => FloatComparator::greaterThanOrEquals($price, $this->amount),
            'lt' => FloatComparator::lessThan($price, $this->amount),
            'lte' => FloatComparator::lessThanOrEquals($price, $this->amount),
            'range' => FloatComparator::greaterThanOrEquals($price, $this->amountFrom) && FloatComparator::lessThanOrEquals($price, $this->amountTo),
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        if ('range' === $this->operator) {
            return \sprintf('between %s and %s', $this->amountFrom, $this->amountTo);
        }

        return $this->trans($this->operator).' '.$this->amount;
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
