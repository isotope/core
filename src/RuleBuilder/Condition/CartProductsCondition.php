<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Util\FloatComparator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'cart', factory: CartConditionFactory::class)]
class CartProductsCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly float $quantity,
        private readonly float $quantityFrom,
        private readonly float $quantityTo,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $quantity = $context->getCart()->getLineItems()->whereType(LineItem::TYPE_PRODUCT)->count();

        return match ($this->operator) {
            'eq' => FloatComparator::equals($quantity, $this->quantity),
            'neq' => FloatComparator::notEquals($quantity, $this->quantity),
            'gt' => FloatComparator::greaterThan($quantity, $this->quantity),
            'gte' => FloatComparator::greaterThanOrEquals($quantity, $this->quantity),
            'lt' => FloatComparator::lessThan($quantity, $this->quantity),
            'lte' => FloatComparator::lessThanOrEquals($quantity, $this->quantity),
            'range' => FloatComparator::greaterThanOrEquals($quantity, $this->quantityFrom) && FloatComparator::lessThanOrEquals($quantity, $this->quantityTo),
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        if ('range' === $this->operator) {
            return \sprintf('between %s and %s', $this->quantityFrom, $this->quantityTo);
        }

        return $this->trans($this->operator).' '.$this->quantity;
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
