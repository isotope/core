<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Config;
use Contao\Date;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Store\StoreManager;

#[AsRuleCondition(category: 'dates', factory: StoreConditionFactory::class)]
class StoreDateCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly int|null $start,
        private readonly int|null $stop,
    ) {
    }

    public function isValid(): bool
    {
        return null !== $this->start || null !== $this->stop;
    }

    public function isApplicable(): bool
    {
        $now = $this->storeManager->getCurrentContext()->getTimestamp()->format('YmdHi');

        if (null !== $this->start && $now < date('YmdHi', $this->start)) {
            return false;
        }

        if (null !== $this->stop && $now > date('YmdHi', $this->stop)) {
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
            return 'before '.Date::parse(Config::get('datimFormat'), $this->stop);
        }

        if (null === $this->stop) {
            return 'later then '.Date::parse(Config::get('datimFormat'), $this->start);
        }

        return \sprintf('between %s and %s', Date::parse(Config::get('datimFormat'), $this->start), Date::parse(Config::get('datimFormat'), $this->stop));
    }

    public function getOperators(): array
    {
        return [];
    }
}
