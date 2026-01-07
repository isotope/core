<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Config;
use Contao\Date;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Store\Context\StoreContext;

#[AsRuleCondition(category: 'dates', factory: StoreConditionFactory::class)]
class StoreTimeCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly int $start,
        private readonly int $stop,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $now = $context->getTimestamp()->format('Hi');

        return $now >= date('Hi', $this->start) && $now <= date('Hi', $this->stop);
    }

    public function getBackendLabel(): string
    {
        return \sprintf('between %s and %s', Date::parse(Config::get('timeFormat'), $this->start), Date::parse(Config::get('timeFormat'), $this->stop));
    }

    public function getOperators(): array
    {
        return [];
    }
}
