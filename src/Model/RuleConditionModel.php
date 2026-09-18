<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $sorting
 * @property int    $tstamp
 * @property string $type
 * @property string $operator
 * @property int    $rule
 * @property string $quantity
 * @property string $quantity_from
 * @property string $quantity_to
 * @property string $amount
 * @property string $amount_from
 * @property string $amount_to
 * @property string $product_types
 * @property string $member_group
 * @property string $config_ids
 * @property string $currencies
 * @property string $start
 * @property string $stop
 * @property string $start_time
 * @property string $stop_time
 * @property string $address_type
 * @property string $countries
 * @property string $subdivisions
 * @property string $postalcodes
 */
class RuleConditionModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_rule_condition';

    /**
     * @return Collection<RuleConditionModel>|null
     */
    public static function findByRuleId(int $ruleId, array $options = []): Collection|null
    {
        $options['order'] ??= 'sorting';

        return self::findBy(['tl_iso_rule_condition.pid=?', 'tl_iso_rule_condition.tstamp>0'], [$ruleId], $options);
    }
}
