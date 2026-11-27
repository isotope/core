<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Weight;

use Isotope\CoreBundle\Store\Weight\WeightUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WeightUnitTest extends TestCase
{
    /**
     * Independent mass definitions in kilograms, rather than the production factors.
     *
     * @see https://www.nist.gov/system/files/documents/2025/12/30/appc-26-HB44-20251222.pdf
     */
    private const array KILOGRAMS_PER_UNIT = [
        'mg' => 0.000001,
        'g' => 0.001,
        'kg' => 1.0,
        't' => 1000.0,
        'ct' => 0.0002,
        'oz' => 0.028349523125,
        'lb' => 0.45359237,
        'st' => 6.35029318,
        'grain' => 0.00006479891,
    ];

    // The existing stone factor is rounded, so allow a small relative error.
    private const float RELATIVE_DELTA = 2e-10;

    #[DataProvider('factorProvider')]
    public function testFactorMatchesMassDefinition(WeightUnit $unit, float $kilograms): void
    {
        $expected = 1 / $kilograms;

        $this->assertEqualsWithDelta($expected, $unit->factor(), $expected * self::RELATIVE_DELTA);
    }

    public static function factorProvider(): iterable
    {
        foreach (WeightUnit::cases() as $unit) {
            yield $unit->value => [$unit, self::KILOGRAMS_PER_UNIT[$unit->value]];
        }
    }

    #[DataProvider('conversionProvider')]
    public function testConversionBetweenAllUnits(float $amount, WeightUnit $source, WeightUnit $target, float $expected): void
    {
        $this->assertEqualsWithDelta($expected, WeightUnit::convert($amount, $source, $target), abs($expected) * self::RELATIVE_DELTA);
    }

    public static function conversionProvider(): iterable
    {
        foreach (WeightUnit::cases() as $source) {
            foreach (WeightUnit::cases() as $target) {
                foreach ([0.0, 1.0, -2.5, 0.125, 1000000.0] as $amount) {
                    $expected = $amount * self::KILOGRAMS_PER_UNIT[$source->value] / self::KILOGRAMS_PER_UNIT[$target->value];

                    yield \sprintf('%s %s to %s', $amount, $source->value, $target->value) => [$amount, $source, $target, $expected];
                }
            }
        }
    }

    #[DataProvider('sameUnitProvider')]
    public function testSameUnitPreservesExactAmount(WeightUnit $unit): void
    {
        $amount = 1.234567891234567;

        $this->assertSame($amount, WeightUnit::convert($amount, $unit, $unit));
    }

    public static function sameUnitProvider(): iterable
    {
        foreach (WeightUnit::cases() as $unit) {
            yield $unit->value => [$unit];
        }
    }

    #[DataProvider('ounceConversionProvider')]
    public function testOunceConversion(float $amount, WeightUnit $source, WeightUnit $target, float $expected): void
    {
        $this->assertEqualsWithDelta($expected, WeightUnit::convert($amount, $source, $target), 0.0000000001);
    }

    public static function ounceConversionProvider(): iterable
    {
        yield 'ounce to grams' => [1.0, WeightUnit::Ounce, WeightUnit::Gram, 28.349523125];
        yield 'grams to ounce' => [28.349523125, WeightUnit::Gram, WeightUnit::Ounce, 1.0];
        yield 'pound to ounces' => [1.0, WeightUnit::Pound, WeightUnit::Ounce, 16.0];
        yield 'ounces to pound' => [16.0, WeightUnit::Ounce, WeightUnit::Pound, 1.0];
    }
}
