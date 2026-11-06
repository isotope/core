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

        $this->assertInstanceOf(CalculatedPrice::class, $restored);
        $this->assertSame(3, $restored->getQuantity());
        $this->assertSame('10%', $restored->getUnitLabel());
        $this->assertTrue($restored->isRange());
        $this->assertSame($price->jsonSerialize(), $restored->jsonSerialize());
        $this->assertEqualsWithDelta(30.0, $restored->reduce()->getUnitPrice(), PHP_FLOAT_EPSILON);
    }

    public function testMissingQuantityDefaultsToOne(): void
    {
        $price = CalculatedPrice::createFromJson('{"unit_price":10,"total_price":10}');

        $this->assertInstanceOf(CalculatedPrice::class, $price);
        $this->assertSame(1, $price->getQuantity());
        $this->assertNull($price->getUnitLabel());
        $this->assertFalse($price->isRange());
    }
}
