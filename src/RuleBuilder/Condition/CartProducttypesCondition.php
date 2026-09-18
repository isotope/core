<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'cart', factory: CartConditionFactory::class)]
class CartProducttypesCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly array $productTypes,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $productTypes = array_map(
            static fn (LineItem $lineItem) => $lineItem->getProduct() instanceof CoreProduct ? $lineItem->getProduct()->getProductType()->id : 0,
            $context->getCart()?->getLineItems()->products()->toArray() ?? [],
        );

        return match ($this->operator) {
            'has_only' => [] === array_diff($productTypes, $this->productTypes),
            'has_all' => $this->productTypes === array_intersect($this->productTypes, $productTypes),
            'has_one' => [] !== array_intersect($productTypes, $this->productTypes),
            'has_none' => [] === array_intersect($this->productTypes, $productTypes),
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        $names = ProductTypeModel::findMultipleByIds($this->productTypes)?->fetchEach('name') ?? [];
        $productTypes = array_replace(array_combine($this->productTypes, $this->productTypes), $names);

        return \sprintf('%s "%s"', $this->trans($this->operator), implode('", "', $productTypes));
    }

    public function getOperators(): array
    {
        $op = ['has_only', 'has_one', 'has_none', 'has_all'];

        return array_combine($op, array_map($this->trans(...), $op));
    }

    private function trans(string $id): string
    {
        return $this->translator->trans('tl_iso_rule_condition.operator.'.$id, [], 'contao_tl_rule_condition');
    }
}
