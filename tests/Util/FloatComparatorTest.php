<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Util;

use Isotope\CoreBundle\Exception\LogicException;
use Isotope\CoreBundle\Util\FloatComparator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FloatComparatorTest extends TestCase
{
    #[DataProvider('comparisonProvider')]
    public function testComparisonMethods(float $a, float $b, int $order): void
    {
        $this->assertSame(0 === $order, FloatComparator::equals($a, $b));
        $this->assertSame(0 !== $order, FloatComparator::notEquals($a, $b));
        $this->assertSame($order < 0, FloatComparator::lessThan($a, $b));
        $this->assertSame($order > 0, FloatComparator::greaterThan($a, $b));
        $this->assertSame($order <= 0, FloatComparator::lessThanOrEquals($a, $b));
        $this->assertSame($order >= 0, FloatComparator::greaterThanOrEquals($a, $b));
    }

    public static function comparisonProvider(): iterable
    {
        yield 'zero' => [0.0, 0.0, 0];
        yield 'signed zero' => [-0.0, 0.0, 0];
        yield 'equal positives' => [42.5, 42.5, 0];
        yield 'equal negatives' => [-42.5, -42.5, 0];
        yield 'less than' => [1.0, 2.0, -1];
        yield 'greater than' => [2.0, 1.0, 1];
        yield 'negative less than' => [-2.0, -1.0, -1];
        yield 'negative greater than' => [-1.0, -2.0, 1];
        yield 'opposite signs less than' => [-1.0, 1.0, -1];
        yield 'opposite signs greater than' => [1.0, -1.0, 1];
        yield 'fractional values' => [1.25, 1.5, -1];
        yield 'large values' => [1000000000000.0, 1000000000001.0, -1];
        yield 'floating point arithmetic' => [0.1 + 0.2, 0.3, 0];
        yield 'within positive tolerance' => [0.0, 0.000000005, 0];
        yield 'within negative tolerance' => [-0.000000005, 0.0, 0];
        yield 'within tolerance across zero' => [-0.0000000025, 0.0000000025, 0];
        yield 'within tolerance near nonzero value' => [1.0, 1.000000005, 0];
        yield 'just below tolerance' => [0.0, 0.000000009999999, 0];
        yield 'positive boundary less than' => [0.0, 0.00000001, -1];
        yield 'positive boundary greater than' => [0.00000001, 0.0, 1];
        yield 'negative boundary less than' => [-0.00000001, 0.0, -1];
        yield 'negative boundary greater than' => [0.0, -0.00000001, 1];
        yield 'just above tolerance less than' => [0.0, 0.000000010000001, -1];
        yield 'just above tolerance greater than' => [0.000000010000001, 0.0, 1];
    }

    #[DataProvider('operatorProvider')]
    public function testCompare(float $a, float $b, string $operator, bool $expected): void
    {
        $this->assertSame($expected, FloatComparator::compare($a, $b, $operator));
    }

    public static function operatorProvider(): iterable
    {
        foreach (self::comparisonProvider() as $name => [$a, $b, $order]) {
            foreach ([
                '=' => 0 === $order,
                '!=' => 0 !== $order,
                '<' => $order < 0,
                '>' => $order > 0,
                '<=' => $order <= 0,
                '>=' => $order >= 0,
            ] as $operator => $expected) {
                yield $name.' '.$operator => [$a, $b, $operator, $expected];
            }
        }
    }

    #[DataProvider('invalidOperatorProvider')]
    public function testUnsupportedOperatorThrows(string $operator): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Operator "'.$operator.'" is not supported.');

        FloatComparator::compare(1.0, 2.0, $operator);
    }

    public static function invalidOperatorProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'double equals' => ['=='];
        yield 'strict equals' => ['==='];
        yield 'alternative not equals' => ['<>'];
        yield 'unknown' => ['invalid'];
    }

    #[DataProvider('castProvider')]
    public function testCast(float $value, float $expected): void
    {
        $previousPrecision = \ini_get('precision');

        ini_set('precision', '14');

        try {
            $this->assertSame($expected, FloatComparator::cast($value));
        } finally {
            ini_set('precision', $previousPrecision);
        }
    }

    public static function castProvider(): iterable
    {
        yield 'zero' => [0.0, 0.0];
        yield 'integer' => [42.0, 42.0];
        yield 'negative fraction' => [-1.25, -1.25];
        yield 'positive arithmetic residue' => [0.1 + 0.2, 0.3];
        yield 'negative arithmetic residue' => [-0.1 - 0.2, -0.3];
        yield 'small exponent' => [1e-20, 1e-20];
        yield 'fraction rounded to string precision' => [123456789.123456, 123456789.12346];
        yield 'large exponent' => [123456789012346.0, 123456789012350.0];
    }
}
