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
        private readonly \DateTimeImmutable $start,
        private readonly \DateTimeImmutable $stop,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $now = $context->getTimestamp()->setTimezone($this->start->getTimezone());
        $start = $now->setTime((int) $this->start->format('H'), (int) $this->start->format('i'), (int) $this->start->format('s'));
        $stop = $now->setTime((int) $this->stop->format('H'), (int) $this->stop->format('i'), (int) $this->stop->format('s'));

        if ($stop < $start) {
            return $now >= $start || $now <= $stop;
        }

        return $now >= $start && $now <= $stop;
    }

    public function getBackendLabel(): string
    {
        return \sprintf('between %s and %s', Date::parse(Config::get('timeFormat'), $this->start->getTimestamp()), Date::parse(Config::get('timeFormat'), $this->stop->getTimestamp()));
    }

    public function getOperators(): array
    {
        return [];
    }
}
