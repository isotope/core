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
 * @property string $amount
 * @property string $amount_from
 * @property string $amount_to
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
class RuleCondition extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_rule_condition';

    /**
     * @return Collection<RuleCondition>|null
     */
    public static function findByRuleId(int $ruleId, array $options = []): Collection|null
    {
        if (!isset($options['order'])) {
            $options['order'] = 'sorting';
        }

        return self::findBy('pid', $ruleId, $options);
    }
}
