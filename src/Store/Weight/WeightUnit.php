<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Weight;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Isotope\CoreBundle\Util\FloatComparator;
use Symfony\Component\Translation\TranslatableMessage;

enum WeightUnit: string implements TranslatableLabelInterface
{
    case Milligram = 'mg';
    case Gram = 'g';
    case Kilogram = 'kg';
    case Metricton = 't';
    case Carat = 'ct';
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
        'ct' => 5000,
        'oz' => 1 / 0.028349523125,
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

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('WGT.'.$this->value.'.0', [], 'contao_default');
    }
}
