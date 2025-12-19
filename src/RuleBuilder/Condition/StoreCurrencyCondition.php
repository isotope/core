<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'store', factory: StoreConditionFactory::class)]
class StoreCurrencyCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly array $currencies,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(): bool
    {
        $hasCurrency = \in_array($this->storeManager->getCurrentContext()->getCurrency()->getCode(), $this->currencies, true);

        return match ($this->operator) {
            'has_one' => $hasCurrency,
            'has_none' => !$hasCurrency,
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        return \sprintf('%s "%s"', $this->trans($this->operator), implode('", "', $this->currencies));
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
