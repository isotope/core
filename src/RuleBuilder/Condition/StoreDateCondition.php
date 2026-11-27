<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Config;
use Contao\Date;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'dates', factory: StoreConditionFactory::class)]
class StoreDateCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
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
            return '-';
        }

        if (null === $this->start) {
            return $this->translator->trans(
                'tl_iso_rule_condition.label.dateStart',
                [Date::parse(Config::get('datimFormat'), $this->stop->getTimestamp())],
                'contao_tl_iso_rule_condition',
            );
        }

        if (null === $this->stop) {
            return $this->translator->trans(
                'tl_iso_rule_condition.label.dateStop',
                [Date::parse(Config::get('datimFormat'), $this->start->getTimestamp())],
                'contao_tl_iso_rule_condition',
            );
        }

        return $this->translator->trans(
            'tl_iso_rule_condition.label.range',
            [
                Date::parse(Config::get('datimFormat'), $this->start->getTimestamp()),
                Date::parse(Config::get('datimFormat'), $this->stop->getTimestamp()),
            ],
            'contao_tl_iso_rule_condition',
        );
    }

    public function getOperators(): array
    {
        return [];
    }
}
