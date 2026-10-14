<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Util;

use Isotope\CoreBundle\Util\PriceUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PriceUtilTest extends TestCase
{
    #[DataProvider('backendPrices')]
    public function testRoundBackendPrice(float|string $price, string $expected): void
    {
        self::assertSame($expected, PriceUtil::roundBackendPrice($price));
    }

    public static function backendPrices(): iterable
    {
        yield 'small price' => [0.00001, '0.00001'];
        yield 'smallest precision' => [0.00000001, '0.00000001'];
        yield 'large price' => [1e20, '100000000000000000000.00'];
        yield 'negative large price' => [-1e20, '-100000000000000000000.00'];
        yield 'integer' => [12.0, '12.00'];
        yield 'one decimal' => [12.3, '12.30'];
        yield 'comma decimal separator' => ['12,345678901', '12.3456789'];
        yield 'round to eight decimals' => ['12.345678909', '12.34567891'];
        yield 'negative zero' => [-0.000000001, '0.00'];
    }
}
