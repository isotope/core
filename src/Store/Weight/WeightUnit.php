<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Weight;

use Isotope\CoreBundle\Util\FloatComparator;

enum WeightUnit: string
{
    case Milligram = 'mg';
    case Gram = 'g';
    case Kilogram = 'kg';
    case Metricton = 't';
    case Carat = 'cz';
    case Ounce = 'oz';
    case Pound = 'lb';
    case Stone = 'st';
    case Grain = 'grain';

    /**
     * Conversion factor from base unit.
     */
    private const array FACTORS = [
        'mg' => 1000000,
        'g' => 1000,
        'kg' => 1,
        't' => 0.001,
        'cz' => 5000,
        'oz' => 35.2733686067,
        'lb' => 2.2046226218487757,
        'st' => 0.1574730444,
        'grain' => 15432.3583529414,
    ];

    public function factor(): float
    {
        return self::FACTORS[$this->value];
    }

    /**
     * Convert from one to another unit.
     */
    public static function convert(float $value, WeightUnit $sourceUnit, WeightUnit $targetUnit): float
    {
        if ($sourceUnit === $targetUnit) {
            return $value;
        }

        $value = FloatComparator::cast($value / $sourceUnit->factor());

        return FloatComparator::cast($value * $targetUnit->factor());
    }
}
