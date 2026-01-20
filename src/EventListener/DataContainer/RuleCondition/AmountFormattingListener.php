<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RuleCondition;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Util\PriceUtil;

class AmountFormattingListener
{
    #[AsCallback('tl_iso_rule_condition', 'fields.amount.load')]
    #[AsCallback('tl_iso_rule_condition', 'fields.amount_from.load')]
    #[AsCallback('tl_iso_rule_condition', 'fields.amount_to.load')]
    public function load(string|null $value): string
    {
        if (null === $value) {
            return '';
        }

        return PriceUtil::formatBackendPrice($value);
    }

    #[AsCallback('tl_iso_rule_condition', 'fields.amount.save')]
    #[AsCallback('tl_iso_rule_condition', 'fields.amount_from.save')]
    #[AsCallback('tl_iso_rule_condition', 'fields.amount_to.save')]
    public function save(string $value): string
    {
        return PriceUtil::roundBackendPrice($value);
    }
}
