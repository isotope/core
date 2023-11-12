<?php

declare(strict_types=1);

namespace Isotope;

use UnitConverter\Calculator\BinaryCalculator;
use UnitConverter\Calculator\CalculatorInterface;
use UnitConverter\Registry\UnitRegistry;
use UnitConverter\UnitConverter;
use UnitConverter\Unit\Mass\Gram;
use UnitConverter\Unit\Mass\Kilogram;
use UnitConverter\Unit\Mass\Milligram;
use UnitConverter\Unit\Mass\Ounce;
use UnitConverter\Unit\Mass\Pound;
use UnitConverter\Unit\Mass\Stone;


class UnitConverterStaticFactory
{
    public static function createUnitConverter(): UnitConverter
    {
        /**
         * @var CalculatorInterface $binaryCalculator
         * Precision 2
         * Round mode 1 = PHP_ROUND_HALF_UP
         */
        $binaryCalculator = new BinaryCalculator(2, 1);

        $units = [
            new Milligram(),
            new Gram(),
            new Kilogram(),
            new Ounce(),
            new Pound(),
            new Stone(),
            // Add more units if needed
        ];

        $unitRegistry = new UnitRegistry($units);

        $unitConverter = new UnitConverter($unitRegistry, $binaryCalculator);

        return $unitConverter;
    }
}
