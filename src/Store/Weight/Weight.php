<?php

namespace Isotope\CoreBundle\Store\Weight;

use Contao\StringUtil;
use Isotope\CoreBundle\Util\FloatComparator;

final readonly class Weight
{
    private float $amount;

    public function __construct(
        float $amount,
        private WeightUnit $unit
    ) {
        $this->amount = FloatComparator::cast($amount);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getAmountInUnit(WeightUnit $unit): float
    {
        return WeightUnit::convert($this->amount, $this->unit, $unit);
    }

    public function getUnit(): WeightUnit
    {
        return $this->unit;
    }

    /**
     * Create weight object from timePeriod widget value
     */
    public static function createFromTimePeriod(string $value): self|null
    {
        $data = StringUtil::deserialize($value);

        if (
            !is_array($data)
            || empty($data['value'])
            || empty($data['unit'])
            || !($unit = WeightUnit::tryFrom($data['unit']))
        ) {
            return null;
        }

        return new self((float) $data['value'], $unit);
    }
}
