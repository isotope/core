<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Config;
use Contao\Date;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Store\Context\StoreContext;

#[AsRuleCondition(category: 'dates', factory: StoreConditionFactory::class)]
class StoreDateCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly \DateTimeImmutable|null $start,
        private readonly \DateTimeImmutable|null $stop,
    ) {
    }

    public function isValid(): bool
    {
        return null !== $this->start || null !== $this->stop;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $now = $context->getTimestamp();

        if (null !== $this->start && $now < $this->start) {
            return false;
        }

        if (null !== $this->stop && $now > $this->stop) {
            return false;
        }

        return true;
    }

    public function getBackendLabel(): string
    {
        if (!$this->isValid()) {
            return '<i>invalid</i>';
        }

        if (null === $this->start) {
            return 'before '.Date::parse(Config::get('datimFormat'), $this->stop?->getTimestamp());
        }

        if (null === $this->stop) {
            return 'later then '.Date::parse(Config::get('datimFormat'), $this->start->getTimestamp());
        }

        return \sprintf('between %s and %s', Date::parse(Config::get('datimFormat'), $this->start->getTimestamp()), Date::parse(Config::get('datimFormat'), $this->stop->getTimestamp()));
    }

    public function getOperators(): array
    {
        return [];
    }
}
