<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Price;

use Isotope\CoreBundle\Store\Price\BasePrice;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use PHPUnit\Framework\TestCase;

final class CalculatedPriceTest extends TestCase
{
    public function testBasePriceSurvivesJsonEncodingAndDecoding(): void
    {
        $price = (new CalculatedPrice(6, 12, quantity: 2))
            ->setBasePrice(new BasePrice(500, 100, '%s per 100g'))
        ;
        $restored = CalculatedPrice::createFromJson(json_encode($price, JSON_THROW_ON_ERROR));

        $this->assertInstanceOf(CalculatedPrice::class, $restored);
        $basePrice = $restored->getBasePrice();
        $this->assertInstanceOf(BasePrice::class, $basePrice);
        $this->assertEqualsWithDelta(500.0, $basePrice->getProductAmount(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(100.0, $basePrice->getBaseAmount(), PHP_FLOAT_EPSILON);
        $this->assertSame('%s per 100g', $basePrice->getLabel());
    }

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
        $this->assertNull($restored->getBasePrice());
        $this->assertEqualsWithDelta(30.0, $restored->reduce()->getUnitPrice(), PHP_FLOAT_EPSILON);
    }

    public function testMissingQuantityDefaultsToOne(): void
    {
        $price = CalculatedPrice::createFromJson('{"unit_price":10,"total_price":10}');

        $this->assertInstanceOf(CalculatedPrice::class, $price);
        $this->assertSame(1, $price->getQuantity());
        $this->assertNull($price->getUnitLabel());
        $this->assertNull($price->getBasePrice());
        $this->assertFalse($price->isRange());
    }
}
