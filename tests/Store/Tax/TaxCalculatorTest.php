<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Tax;

use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Store\Tax\TaxRate;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TaxCalculatorTest extends TestCase
{
    #[DataProvider('calculateNetPriceProvider')]
    public function testCalculateNetPrice(array $taxes, TaxRate|null $included, float $price, PriceDisplay $priceDisplay, float $expected): void
    {
        $taxRates = new TaxRateCollection($taxes);
        $taxRates->setIncluded($included);

        $calculator = new TaxCalculator($this->createStub(RuleMatcher::class));

        $result = $calculator->calculateNetPrice($price, $taxRates, $priceDisplay);

        $this->assertSame(round($expected, 8), round($result, 8));
    }

    public static function calculateNetPriceProvider(): iterable
    {
        yield 'Does not change net price without taxes' => [
            [],
            null,
            100,
            PriceDisplay::Net,
            100,
        ];

        yield 'Does not change gross price without taxes' => [
            [],
            null,
            100,
            PriceDisplay::Gross,
            100,
        ];

        yield 'Subtracts included tax 1' => [
            [],
            new TaxRate('1', 19),
            100,
            PriceDisplay::Net,
            100 / 119 * 100,
        ];

        yield 'Subtracts included tax 2' => [
            [],
            new TaxRate('1', 7),
            100,
            PriceDisplay::Net,
            100 / 107 * 100,
        ];

        yield 'Subtracts included tax 3' => [
            [],
            new TaxRate('1', 8.1),
            100,
            PriceDisplay::Net,
            100 / 108.1 * 100,
        ];

        yield 'Subtracts 50% of included tax' => [
            [],
            new TaxRate('1', 10, 50),
            100,
            PriceDisplay::Net,
            100 - 50 / 110 * 10,
        ];

        yield 'Does not subtract taxes for net price' => [
            [new TaxRate('1', 19)],
            null,
            100,
            PriceDisplay::Net,
            100,
        ];

        yield 'Does not subtract taxes for gross price' => [
            [new TaxRate('1', 19)],
            null,
            100,
            PriceDisplay::Gross,
            100,
        ];

        yield 'Subtracts added tax for fixed price' => [
            [new TaxRate('1', 19)],
            null,
            100,
            PriceDisplay::Fixed,
            100 / 119 * 100,
        ];

        yield 'Subtracts 42% of added tax for fixed price' => [
            [new TaxRate('1', 19, 42)],
            null,
            100,
            PriceDisplay::Fixed,
            100 - 42 / 119 * 19,
        ];

        yield 'Subtracts added taxes for fixed price' => [
            [new TaxRate('1', 19), new TaxRate('2', 10)],
            null,
            100,
            PriceDisplay::Fixed,
            100 - (100 / 119 * 19) - (100 / 110 * 10),
        ];

        yield 'Does not subtract the included tax for fixed price' => [
            [],
            new TaxRate('1', 19),
            100,
            PriceDisplay::Fixed,
            100,
        ];
    }

    #[DataProvider('combineTaxRatesProvider')]
    public function testCombineTaxRates(TaxRateCollection $taxes1, TaxRateCollection $taxes2, float $price1, float $price2, array $expectedRates, array $expectedPercentages): void
    {
        $calculator = new TaxCalculator($this->createStub(RuleMatcher::class));
        $result = $calculator->combineTaxRates($taxes1, $taxes2, $price1, $price2);

        $this->assertCount(count($expectedRates), $result);

        foreach ($result as $i => $tax) {
            $this->assertSame(round($tax->getTaxRate(), 8), round($expectedRates[$i], 8));
            $this->assertSame(round($tax->getPercentage(), 8), round($expectedPercentages[$i], 8));
        }
    }

    public static function combineTaxRatesProvider(): iterable
    {
        yield [
            new TaxRateCollection(),
            new TaxRateCollection(),
            0,
            0,
            [],
            [],
        ];

        yield [
            new TaxRateCollection(),
            new TaxRateCollection([new TaxRate('1', 10)]),
            100,
            100,
            ['1' => 10],
            ['1' => 50],
        ];

        yield [
            new TaxRateCollection([new TaxRate('2', 19)]),
            new TaxRateCollection(),
            100,
            100,
            ['2' => 19],
            ['2' => 50],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19)]),
            new TaxRateCollection([new TaxRate('1', 19)]),
            100,
            100,
            ['1' => 19],
            ['1' => 100],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19)]),
            new TaxRateCollection([new TaxRate('2', 7)]),
            100,
            100,
            ['1' => 19, '2' => 7],
            ['1' => 50, '2' => 50],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19)]),
            new TaxRateCollection([new TaxRate('2', 7)]),
            600,
            400,
            ['1' => 19, '2' => 7],
            ['1' => 60, '2' => 40],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19), new TaxRate('2', 7)]),
            new TaxRateCollection([new TaxRate('2', 7)]),
            200,
            100,
            ['1' => 19, '2' => 7],
            ['1' => 66.66666667, '2' => 100],
        ];
    }

    #[DataProvider('combineCalculatedTaxesProvider')]
    public function testCombineCalculatedTaxes(CalculatedTaxCollection $taxes1, CalculatedTaxCollection $taxes2, array $expectedTaxes, array $expectedPrices, array $expectedNumbers): void
    {
        $calculator = new TaxCalculator($this->createStub(RuleMatcher::class));
        $result = $calculator->combineCalculatedTaxes($taxes1, $taxes2);

        $this->assertCount(\count($expectedTaxes), $result);

        /**
         * @var CalculatedTax $tax
         */
        foreach ($result as $i => $tax) {
            $this->assertSame($tax->getTax(), $expectedTaxes[$i]);
            $this->assertSame($tax->getPrice(), $expectedPrices[$i]);
            $this->assertSame($tax->getNumber(), $expectedNumbers[$i]);
        }
    }

    public static function combineCalculatedTaxesProvider(): iterable
    {
        yield [
            new CalculatedTaxCollection(),
            new CalculatedTaxCollection(),
            [],
            [],
            [],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection(),
            ['1' => 10.0],
            ['1' => 100.0],
            ['1' => 1],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            ['1' => 20.0],
            ['1' => 200.0],
            ['1' => 1],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection([new CalculatedTax('2', 10.0, 7, 100.0)]),
            ['1' => 10.0, '2' => 10.0],
            ['1' => 100.0, '2' => 100.0],
            ['1' => 1, '2' => 2],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection([new CalculatedTax('2', 10.0, 7, 100.0)]),
            ['1' => 10.0, '2' => 10.0],
            ['1' => 100.0, '2' => 100.0],
            ['1' => 1, '2' => 2],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0), new CalculatedTax('4', 4.0, 7, 42.0)]),
            new CalculatedTaxCollection([new CalculatedTax('4', 10.0, 7, 100.0)]),
            ['1' => 10.0, '4' => 14.0],
            ['1' => 100.0, '4' => 142.0],
            ['1' => 1, '4' => 2],
        ];
    }
}
