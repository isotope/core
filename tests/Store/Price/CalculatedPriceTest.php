<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Price;

use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use PHPUnit\Framework\TestCase;

final class CalculatedPriceTest extends TestCase
{
    public function testJsonEncodingAndDecoding(): void
    {
        $price = (new CalculatedPrice(10, 30, quantity: 3))
            ->setUnitLabel('10%')
            ->setRange(true)
        ;
        $restored = CalculatedPrice::createFromJson(json_encode($price, JSON_THROW_ON_ERROR));

        self::assertNotNull($restored);
        self::assertSame(3, $restored->getQuantity());
        self::assertSame('10%', $restored->getUnitLabel());
        self::assertTrue($restored->isRange());
        self::assertSame($price->jsonSerialize(), $restored->jsonSerialize());
        self::assertSame(30.0, $restored->reduce()->getUnitPrice());
    }

    public function testMissingQuantityDefaultsToOne(): void
    {
        $price = CalculatedPrice::createFromJson('{"unit_price":10,"total_price":10}');

        self::assertNotNull($price);
        self::assertSame(1, $price->getQuantity());
        self::assertNull($price->getUnitLabel());
        self::assertFalse($price->isRange());
    }
}
