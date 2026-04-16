<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidOperatorException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'store', factory: StoreConditionFactory::class)]
class StoreConfigCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $operator,
        private readonly array $configs,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $hasConfig = \in_array($context->getConfigId(), $this->configs, true);

        return match ($this->operator) {
            'has_one' => $hasConfig,
            'has_none' => !$hasConfig,
            default => throw new InvalidOperatorException($this->operator, self::class),
        };
    }

    public function getBackendLabel(): string
    {
        $names = ConfigModel::findMultipleByIds($this->configs)?->fetchEach('name') ?? [];
        $names = array_replace(array_combine($this->configs, $this->configs), $names);

        return \sprintf('%s "%s"', $this->trans($this->operator), implode('", "', $names));
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
